<?php

namespace App\Http\Controllers\Professor;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseResource;
use App\Models\CourseSection;
use App\Models\Lesson;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(): View
    {
        $courses = Course::query()
            ->where('teacher_id', Auth::id())
            ->with('subject.semester.program.level')
            ->orderByDesc('created_at')
            ->get();

        return view('professor.dashboard', [
            'courses' => $courses,
        ]);
    }

    public function show(Course $course): View
    {
        $this->authorizeOwnership($course);

        $course->load([
            'subject.semester.program.level',
            'sections' => fn ($query) => $query->orderBy('sort_order'),
            'sections.lessons' => fn ($query) => $query->orderBy('sort_order'),
            'lessons' => fn ($query) => $query->whereNull('course_section_id')->orderBy('sort_order'),
            'resources' => fn ($query) => $query->orderBy('sort_order'),
        ]);

        $students = $this->enrolledStudents($course);

        return view('professor.courses.show', [
            'course' => $course,
            'students' => $students,
            'resourcesByType' => $course->resources->groupBy('type'),
        ]);
    }

    public function storeSection(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
        ]);

        CourseSection::create([
            'course_id' => $course->id,
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'sort_order' => $course->sections()->count(),
            'is_active' => true,
        ]);

        return back()->with('success', 'La section a été créée.');
    }

    public function storeLesson(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $data = $request->validate([
            'course_section_id' => ['nullable', 'exists:course_sections,id'],
            'title' => ['required', 'string', 'max:150'],
            'content' => ['nullable', 'string'],
            'video_url' => ['nullable', 'url', 'max:255'],
            'duration_minutes' => ['nullable', 'integer', 'min:0'],
            'is_preview' => ['nullable', 'boolean'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        Lesson::create([
            'uuid' => (string) Str::uuid(),
            'course_id' => $course->id,
            'course_section_id' => $data['course_section_id'] ?? null,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($course->id.'-'.$data['title']),
            'content' => $data['content'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? 0,
            'is_preview' => $request->boolean('is_preview'),
            'is_published' => $request->boolean('is_published'),
            'sort_order' => $course->lessons()->count(),
        ]);

        return back()->with('success', 'La leçon a été créée.');
    }

    public function togglePublishLesson(Lesson $lesson): RedirectResponse
    {
        $this->authorizeOwnership($lesson->course);

        $lesson->update(['is_published' => ! $lesson->is_published]);

        return back()->with(
            'success',
            $lesson->is_published ? 'Leçon publiée.' : 'Leçon dépubliée.'
        );
    }

    public function destroyLesson(Lesson $lesson): RedirectResponse
    {
        $this->authorizeOwnership($lesson->course);

        $lesson->delete();

        return back()->with('success', 'La leçon a été supprimée.');
    }

    public function storeResource(Request $request, Course $course): RedirectResponse
    {
        $this->authorizeOwnership($course);

        $data = $request->validate([
            'type' => ['required', 'in:cours,td,exercice,resume'],
            'title' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:1000'],
            'file' => ['required', 'file', 'max:20480'], // 20 Mo max
        ]);

        $file = $request->file('file');
        $path = $file->store('course-resources/'.$course->id, 'public');

        CourseResource::create([
            'uuid' => (string) Str::uuid(),
            'course_id' => $course->id,
            'uploaded_by' => Auth::id(),
            'type' => $data['type'],
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'is_published' => true,
            'sort_order' => $course->resources()->count(),
        ]);

        return back()->with('success', 'Le fichier a été mis en ligne.');
    }

    public function destroyResource(CourseResource $resource): RedirectResponse
    {
        $this->authorizeOwnership($resource->course);

        \Illuminate\Support\Facades\Storage::disk($resource->disk)->delete($resource->path);
        $resource->delete();

        return back()->with('success', 'Le fichier a été supprimé.');
    }

    private function authorizeOwnership(Course $course): void
    {
        abort_unless(
            $course->teacher_id === Auth::id(),
            403,
            'Ce cours ne vous est pas attribué.'
        );
    }

    /**
     * Étudiants ayant accès au module (matière) de ce cours,
     * via un pack module ou un pack semestre actif.
     */
    private function enrolledStudents(Course $course)
    {
        $subject = $course->subject;

        return User::query()
            ->whereHas('packEnrollments', function ($query) use ($subject) {
                $query->where('status', 'active')
                    ->whereHas('pack', function ($query) use ($subject) {
                        $query->where(function ($query) use ($subject) {
                            $query->where('type', 'module')
                                ->where('subject_id', $subject->id);
                        })->orWhere(function ($query) use ($subject) {
                            $query->where('type', 'semestre')
                                ->where('semester_id', $subject->semester_id);
                        });
                    });
            })
            ->orderBy('name')
            ->get();
    }

    private function uniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $number = 2;

        while (Lesson::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$number;
            $number++;
        }

        return $slug;
    }
}
