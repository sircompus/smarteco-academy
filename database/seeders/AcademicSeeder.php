<?php

namespace Database\Seeders;

use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $structure = [
            'Licence' => [
                'programs' => ['Informatique de gestion', 'Génie logiciel', 'Réseaux et systèmes'],
                'duration_semesters' => 6,
            ],
            'Master' => [
                'programs' => ['Intelligence artificielle', 'Cybersécurité', 'Data Engineering'],
                'duration_semesters' => 4,
            ],
            'Formation continue' => [
                'programs' => ['Développement web', 'Analyse de données'],
                'duration_semesters' => 2,
            ],
        ];

        $sortOrder = 0;

        foreach ($structure as $levelName => $data) {
            $level = AcademicLevel::query()->updateOrCreate(
                ['slug' => Str::slug($levelName)],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $levelName,
                    'description' => "Programmes de niveau {$levelName}.",
                    'is_active' => true,
                    'sort_order' => $sortOrder,
                ]
            );

            $programSortOrder = 0;

            foreach ($data['programs'] as $programName) {
                AcademicProgram::query()->updateOrCreate(
                    ['slug' => Str::slug($levelName.'-'.$programName)],
                    [
                        'uuid' => (string) Str::uuid(),
                        'academic_level_id' => $level->id,
                        'name' => $programName,
                        'description' => "Filière {$programName} — {$levelName}.",
                        'duration_semesters' => $data['duration_semesters'],
                        'is_active' => true,
                        'sort_order' => $programSortOrder,
                    ]
                );

                $programSortOrder++;
            }

            $sortOrder++;
        }
    }
}
