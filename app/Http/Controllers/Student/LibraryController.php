<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function index(Request $request): View
    {
        $subjects = Subject::query()
            ->with('semester.program.level')
            ->where('is_active', true)
            ->get()
            ->sortBy(function ($subject) {
                return sprintf(
                    '%03d-%s-%03d-%03d',
                    $subject->semester?->program?->level?->sort_order ?? 999,
                    $subject->semester?->program?->name ?? '',
                    $subject->semester?->number ?? 999,
                    $subject->sort_order ?? 999
                );
            })
            ->values();

        $selectedSubject = null;
        $resourcesByProfessor = collect();
        $hasAccess = false;

        if ($request->filled('subject_id')) {
            $selectedSubject = $subjects->firstWhere('id', (int) $request->integer('subject_id'));

            if ($selectedSubject) {
                $resourcesByProfessor = $selectedSubject->resources()
                    ->where('is_published', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->groupBy(fn ($resource) => $resource->professor_name ?: 'Professeur non renseigné')
                    ->map(fn ($group) => $group->groupBy('type'))
                    ->sortKeys();

                $hasAccess = Auth::user()->hasAccessToSubject($selectedSubject);
            }
        }

        return view('student.library.index', [
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'resourcesByProfessor' => $resourcesByProfessor,
            'hasAccess' => $hasAccess,
        ]);
    }
}
