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
            'sections',
            'lessons',
        ]);

        return view('student.courses.show', [
            'course' => $course,
        ]);
    }
}