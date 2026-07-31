<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->published()
            ->with([
                'subject.semester.program.level',
                'teacher',
            ])
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('student.courses.index', [
            'courses' => $courses,
        ]);
    }

    public function show(Course $course): View
    {
        abort_unless(
            $course->status === 'published'
            && $course->published_at !== null,
            404
        );

        $course->load([
            'subject.semester.program.level',
            'teacher',
            'sections' => function ($query) {
                $query->where('is_active', true)->orderBy('sort_order');
            },
            'sections.lessons' => function ($query) {
                $query->where('is_published', true)->orderBy('sort_order');
            },
            'lessons' => function ($query) {
                $query->whereNull('course_section_id')
                    ->where('is_published', true)
                    ->orderBy('sort_order');
            },
            'resources' => function ($query) {
                $query->where('is_published', true)->orderBy('sort_order');
            },
        ]);

        $hasAccess = auth()->user()->hasAccessToSubject($course->subject);

        return view('student.courses.show', [
            'course' => $course,
            'hasAccess' => $hasAccess,
            'resourcesByType' => $course->resources->groupBy('type'),
        ]);
    }
}