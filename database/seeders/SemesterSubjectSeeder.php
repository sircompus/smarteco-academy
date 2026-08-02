<?php

namespace Database\Seeders;

use App\Models\AcademicLevel;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SemesterSubjectSeeder extends Seeder
{
    /**
     * Nombre de modules générés automatiquement par semestre.
     */
    private const MODULES_PER_SEMESTER = 7;

    public function run(): void
    {
        // DEUG : semestres 1 à 4
        $this->generateForLevel('deug', 1, 4);

        // Licence : semestres 5 à 6 (suite de numérotation du DEUG)
        $this->generateForLevel('licence', 5, 6);
    }

    private function generateForLevel(string $levelSlug, int $firstNumber, int $lastNumber): void
    {
        $level = AcademicLevel::query()
            ->where('slug', $levelSlug)
            ->with('programs')
            ->first();

        if (! $level) {
            return;
        }

        foreach ($level->programs as $program) {
            $sortOrder = 0;

            for ($number = $firstNumber; $number <= $lastNumber; $number++) {
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
                        'sort_order' => $sortOrder,
                    ]
                );

                $this->generateModules($semester, $program->name);

                $sortOrder++;
            }
        }
    }

    private function generateModules(Semester $semester, string $programName): void
    {
        for ($moduleNumber = 1; $moduleNumber <= self::MODULES_PER_SEMESTER; $moduleNumber++) {
            $name = "Module {$moduleNumber}";

            $slug = Str::slug(
                $programName.'-s'.$semester->number.'-module-'.$moduleNumber
            );

            Subject::query()->updateOrCreate(
                ['slug' => $slug],
                [
                    'uuid' => (string) Str::uuid(),
                    'semester_id' => $semester->id,
                    'name' => $name,
                    'code' => 'S'.$semester->number.'-M'.$moduleNumber,
                    'credits' => 0,
                    'coefficient' => 1,
                    'is_active' => true,
                    'sort_order' => $moduleNumber,
                ]
            );
        }
    }
}
