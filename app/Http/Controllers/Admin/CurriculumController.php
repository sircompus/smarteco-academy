<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicProgram;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CurriculumController extends Controller
{
    public function index(Request $request): View
    {
        $programs = AcademicProgram::query()
            ->with('level')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $selectedProgram = null;
        $textValue = '';

        if ($request->filled('program_id')) {
            $selectedProgram = $programs->firstWhere('id', (int) $request->integer('program_id'));

            if ($selectedProgram) {
                $textValue = $this->buildTextFromProgram($selectedProgram);
            }
        }

        return view('admin.centre.curriculum.index', [
            'programs' => $programs,
            'selectedProgram' => $selectedProgram,
            'textValue' => $textValue,
        ]);
    }

    public function sync(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academic_program_id' => ['required', 'exists:academic_programs,id'],
            'structure' => ['required', 'string'],
        ]);

        $program = AcademicProgram::findOrFail($data['academic_program_id']);

        $parsed = $this->parseStructure($data['structure']);

        if ($parsed->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['structure' => 'Aucun semestre reconnu. Vérifie le format (voir exemple ci-dessous).']);
        }

        $semesterCount = 0;
        $moduleCount = 0;
        $moduleDeletedCount = 0;

        foreach ($parsed as $number => $moduleNames) {
            $semester = Semester::query()->updateOrCreate(
                [
                    'academic_program_id' => $program->id,
                    'number' => $number,
                ],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => "Semestre {$number}",
                    'code' => "S{$number}",
                    'is_active' => true,
                    'sort_order' => $number,
                ]
            );

            $semesterCount++;

            $existingSubjects = $semester->subjects()->get()->keyBy(
                fn ($subject) => Str::lower(trim($subject->name))
            );

            $keptNames = [];

            foreach ($moduleNames as $position => $moduleName) {
                $key = Str::lower(trim($moduleName));
                $keptNames[] = $key;

                if ($existingSubjects->has($key)) {
                    $existingSubjects->get($key)->update([
                        'sort_order' => $position,
                        'is_active' => true,
                    ]);
                    continue;
                }

                Subject::create([
                    'uuid' => (string) Str::uuid(),
                    'semester_id' => $semester->id,
                    'name' => trim($moduleName),
                    'slug' => $this->uniqueSlug(
                        $program->name.'-s'.$number.'-'.$moduleName
                    ),
                    'credits' => 0,
                    'coefficient' => 1,
                    'is_active' => true,
                    'sort_order' => $position,
                ]);

                $moduleCount++;
            }

            // Supprime les modules qui ne sont plus dans la liste collée.
            foreach ($existingSubjects as $key => $subject) {
                if (! in_array($key, $keptNames, true)) {
                    $subject->delete();
                    $moduleDeletedCount++;
                }
            }
        }

        return redirect()
            ->route('admin.centre.curriculum.index', ['program_id' => $program->id])
            ->with(
                'success',
                "Synchronisé : {$semesterCount} semestre(s), {$moduleCount} module(s) ajouté(s), {$moduleDeletedCount} module(s) retiré(s)."
            );
    }

    /**
     * Transforme un texte du type :
     *
     * S1
     * Microéconomie
     * Comptabilité générale
     *
     * S2
     * ...
     *
     * en tableau [numéro_semestre => [nom_module, ...]].
     *
     * @return \Illuminate\Support\Collection<int, array<int, string>>
     */
    private function parseStructure(string $raw): \Illuminate\Support\Collection
    {
        $lines = preg_split('/\R/', $raw);

        $result = collect();
        $currentSemester = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (preg_match('/^S\s*(\d{1,2})$/i', $line, $matches)) {
                $currentSemester = (int) $matches[1];
                $result->put($currentSemester, $result->get($currentSemester, []));
                continue;
            }

            if ($currentSemester !== null) {
                $existing = $result->get($currentSemester, []);
                $existing[] = $line;
                $result->put($currentSemester, $existing);
            }
        }

        return $result;
    }

    private function buildTextFromProgram(AcademicProgram $program): string
    {
        $semesters = $program->semesters()
            ->with(['subjects' => function ($query) {
                $query->orderBy('sort_order')->orderBy('name');
            }])
            ->orderBy('number')
            ->get();

        $lines = [];

        foreach ($semesters as $semester) {
            $lines[] = 'S'.$semester->number;

            foreach ($semester->subjects as $subject) {
                $lines[] = $subject->name;
            }

            $lines[] = '';
        }

        return trim(implode("\n", $lines));
    }

    private function uniqueSlug(string $value): string
    {
        $baseSlug = Str::slug($value);
        $slug = $baseSlug;
        $number = 2;

        while (Subject::withTrashed()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$number;
            $number++;
        }

        return $slug;
    }
}
