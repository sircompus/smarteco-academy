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
            'Bac' => [
                'programs' => [],
                'duration_semesters' => null,
            ],
            'DEUG' => [
                'programs' => [
                    'Tronc commun en économie',
                    'Tronc commun en gestion',
                ],
                'duration_semesters' => 4,
            ],
            'Licence' => [
                'programs' => [
                    'Comptabilité Finance et Fiscalité',
                    'Commerce et Marketing',
                    'Management Ressources Humaines',
                    'Économétrie',
                ],
                'duration_semesters' => 6,
            ],
            'Master' => [
                'programs' => [],
                'duration_semesters' => 4,
            ],
            'Formation professionnelle' => [
                'programs' => [],
                'duration_semesters' => null,
            ],
            'Formation continue' => [
                'programs' => [],
                'duration_semesters' => null,
            ],
            'Formation pratique' => [
                'programs' => [],
                'duration_semesters' => null,
            ],
        ];

        $levelSortOrder = 0;

        foreach ($structure as $levelName => $data) {
            $level = AcademicLevel::query()->updateOrCreate(
                ['slug' => Str::slug($levelName)],
                [
                    'uuid' => (string) Str::uuid(),
                    'name' => $levelName,
                    'description' => "Programmes de niveau {$levelName}.",
                    'is_active' => true,
                    'sort_order' => $levelSortOrder,
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

            $levelSortOrder++;
        }
    }
}
