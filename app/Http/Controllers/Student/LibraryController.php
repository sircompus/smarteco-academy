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
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $selectedSubject = null;
        $resourcesByType = collect();
        $hasAccess = false;

        if ($request->filled('subject_id')) {
            $selectedSubject = $subjects->firstWhere('id', (int) $request->integer('subject_id'));

            if ($selectedSubject) {
                $resourcesByType = $selectedSubject->resources()
                    ->where('is_published', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->groupBy('type');

                $hasAccess = Auth::user()->hasAccessToSubject($selectedSubject);
            }
        }

        return view('student.library.index', [
            'subjects' => $subjects,
            'selectedSubject' => $selectedSubject,
            'resourcesByType' => $resourcesByType,
            'hasAccess' => $hasAccess,
        ]);
    }
}
