<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AtsScoreService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CvController extends Controller
{
    public function index(): View
    {
        $users = User::query()
            ->whereHas('roles', function ($query) {
                $query->whereIn('name', ['etudiant', 'stagiaire']);
            })
            ->with('cvProfile')
            ->orderBy('name')
            ->get();

        return view('admin.cv.index', ['users' => $users]);
    }

    public function show(User $user): View
    {
        $profile = $user->cvProfile;

        if ($profile) {
            $profile->load([
                'educations', 'experiences', 'skills',
                'languages', 'certifications', 'projects',
            ]);
        }

        $ats = $profile ? app(AtsScoreService::class)->evaluate($profile) : null;

        return view('admin.cv.show', [
            'targetUser' => $user,
            'profile' => $profile,
            'ats' => $ats,
        ]);
    }

    public function showCv(Request $request, User $user): View
    {
        $profile = $user->cvProfile()->firstOrFail();
        $profile->load(['educations', 'experiences', 'skills', 'languages', 'certifications', 'projects']);

        $requestedTemplate = $request
            ->string('template')
            ->toString();

        $template = in_array(
            $requestedTemplate,
            ['classique', 'moderne'],
            true
        )
            ? $requestedTemplate
            : $profile->cv_template;

        $view = $template === 'moderne'
            ? 'student.cv.templates.moderne'
            : 'student.cv.templates.classique';

        return view($view, ['profile' => $profile, 'layout' => 'layouts.admin']);
    }

    public function showAts(User $user): View
    {
        $profile = $user->cvProfile()->firstOrFail();
        $profile->load(['educations', 'experiences', 'skills', 'languages', 'certifications', 'projects']);

        return view('student.cv.templates.ats', ['profile' => $profile, 'layout' => 'layouts.admin']);
    }
}
