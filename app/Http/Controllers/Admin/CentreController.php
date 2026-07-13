<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use App\Models\Course;
use App\Models\Semester;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CentreController extends Controller
{
    public function index(): View
    {
        return view('admin.centre.index', [
            'levels' => AcademicLevel::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),

            'programs' => AcademicProgram::query()
                ->with('level')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),

            'semesters' => Semester::query()
                ->with('program.level')
                ->orderBy('sort_order')
                ->orderBy('number')
                ->get(),

            'subjects' => Subject::query()
                ->with('semester.program.level')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),

            'professors' => User::query()
                ->whereHas('roles', function ($query) {
                    $query->where('name', 'professeur');
                })
                ->orderBy('name')
                ->get(),

            'courses' => Course::query()
                ->with([
                    'subject.semester.program.level',
                    'teacher',
                ])
                ->latest()
                ->get(),
        ]);
    }

    public function storeLevel(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
                'unique:academic_levels,name',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        AcademicLevel::create([
            'uuid' => (string) Str::uuid(),
            'name' => $data['name'],
            'slug' => $this->uniqueSlug(
                $data['name'],
                AcademicLevel::class
            ),
            'description' => $data['description'] ?? null,
            'is_active' => true,
        ]);

        return back()->with(
            'success',
            'Le niveau a été créé.'
        );
    }

    public function storeProgram(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academic_level_id' => [
                'required',
                'exists:academic_levels,id',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'duration_semesters' => [
                'nullable',
                'integer',
                'min:1',
                'max:20',
            ],
        ]);

        AcademicProgram::create([
            'uuid' => (string) Str::uuid(),
            'academic_level_id' => $data['academic_level_id'],
            'name' => $data['name'],
            'slug' => $this->uniqueSlug(
                $data['name'],
                AcademicProgram::class
            ),
            'description' => $data['description'] ?? null,
            'duration_semesters' => $data['duration_semesters'] ?? null,
            'is_active' => true,
        ]);

        return back()->with(
            'success',
            'La filière a été créée.'
        );
    }

    public function storeSemester(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academic_program_id' => [
                'required',
                'exists:academic_programs,id',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'code' => [
                'nullable',
                'string',
                'max:30',
            ],
            'number' => [
                'required',
                'integer',
                'min:1',
                'max:20',
                Rule::unique('semesters', 'number')
                    ->where(function ($query) use ($request) {
                        return $query->where(
                            'academic_program_id',
                            $request->integer('academic_program_id')
                        );
                    }),
            ],
        ]);

        Semester::create([
            'uuid' => (string) Str::uuid(),
            'academic_program_id' => $data['academic_program_id'],
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'number' => $data['number'],
            'is_active' => true,
        ]);

        return back()->with(
            'success',
            'Le semestre a été créé.'
        );
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'semester_id' => [
                'required',
                'exists:semesters,id',
            ],
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'credits' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'coefficient' => [
                'nullable',
                'numeric',
                'min:0',
            ],
        ]);

        Subject::create([
            'uuid' => (string) Str::uuid(),
            'semester_id' => $data['semester_id'],
            'name' => $data['name'],
            'slug' => $this->uniqueSlug(
                $data['name'],
                Subject::class
            ),
            'code' => $data['code'] ?? null,
            'description' => $data['description'] ?? null,
            'credits' => $data['credits'] ?? 0,
            'coefficient' => $data['coefficient'] ?? 1,
            'is_active' => true,
        ]);

        return back()->with(
            'success',
            'La matière a été créée.'
        );
    }

    public function storeCourse(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'subject_id' => [
                'required',
                'exists:subjects,id',
            ],
            'teacher_id' => [
                'nullable',
                'exists:users,id',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'summary' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        Course::create([
            'uuid' => (string) Str::uuid(),
            'subject_id' => $data['subject_id'],
            'teacher_id' => $data['teacher_id'] ?? null,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug(
                $data['title'],
                Course::class
            ),
            'summary' => $data['summary'] ?? null,
            'description' => $data['description'] ?? null,
            'status' => 'draft',
        ]);

        return back()->with(
            'success',
            'Le cours a été créé comme brouillon.'
        );
    }

    public function publishCourse(Course $course): RedirectResponse
    {
        $course->update([
            'status' => 'published',
            'published_at' => now(),
        ]);

        return back()->with(
            'success',
            'Le cours a été publié.'
        );
    }

    private function uniqueSlug(
        string $value,
        string $modelClass
    ): string {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $number = 2;

        while (
            $modelClass::withTrashed()
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $number;
            $number++;
        }

        return $slug;
    }
}