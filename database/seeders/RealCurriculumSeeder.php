<?php

namespace Database\Seeders;

use App\Models\AcademicProgram;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class RealCurriculumSeeder extends Seeder
{
    /**
     * Reproduit exactement le vrai programme d'Ali (retranscrit depuis ses
     * captures d'écran d'origine), de façon permanente cette fois — plus
     * besoin de repasser par la saisie manuelle du Générateur de cursus
     * si la base doit un jour être réinitialisée.
     *
     * Structure : [Nom exact de la filière (AcademicProgram.name)] => [
     *     numéro_semestre => [liste des modules],
     * ]
     */
    public function run(): void
    {
        $troncCommunS1 = [
            'Introduction aux sciences de gestion',
            'Comptabilité générale 1',
            "Introduction à l'étude de droit",
            'Microéconomie 1',
            'Mathématiques',
            'Macroéconomie 1',
            'Méthodologie du travail universitaire',
        ];

        $troncCommunS2Gestion = [
            'Management fondamental',
            'Comptabilité générale 2',
            'Statistique descriptive',
            'Macroéconomie 2',
            'Mathématiques financières',
            'Microéconomie 2',
            'Compétence numérique et IA',
        ];

        $troncCommunS2Economie = [
            'Management',
            'Comptabilité générale 2',
            'Statistique descriptive',
            'Macroéconomie 2',
            'Mathématiques financières',
            'Microéconomie 2',
            'Compétence numérique et IA',
        ];

        $troncCommunS3 = [
            'Comptabilité analytique de gestion',
            'Economie du Maroc',
            'Management des organisations',
            'Probabilité',
            "Droit de l'entreprise",
            'Sociologie des organisations',
            'Langue étrangère',
        ];

        $troncCommunS4 = [
            'Analyse financière',
            'Marketing de base',
            'Comptabilité des sociétés',
            'Introduction à la GRH',
            'Informatique de gestion',
            'Analyse et traitement des données',
            'Langue étrangère',
        ];

        $structure = [
            'Tronc commun en gestion' => [
                1 => $troncCommunS1,
                2 => $troncCommunS2Gestion,
                3 => $troncCommunS3,
                4 => $troncCommunS4,
            ],
            'Tronc commun en économie' => [
                1 => $troncCommunS1,
                2 => $troncCommunS2Economie,
                3 => $troncCommunS3,
                4 => $troncCommunS4,
            ],
            'Management Ressources Humaines' => [
                5 => [
                    'Contrôle de gestion social',
                    'Gestion de la paie',
                    'Outils GRH',
                    'Gestion prévisionnelle des emplois et compétences',
                    'Droit social',
                    'Langues étrangères 2',
                    'Communication professionnelle et outils digitaux',
                ],
                6 => [
                    'Management stratégique',
                    'Gestion des relations sociales',
                    'Intelligence artificielle et SIRH',
                    'Entrepreneuriat et gestion de projet',
                    'Audit social',
                    'Langues étrangères 2',
                    'Culture entrepreneuriale',
                ],
            ],
            'Comptabilité Finance et Fiscalité' => [
                5 => [
                    'Contrôle de gestion',
                    "Choix d'investissement et modes de financement",
                    'Fiscalité des entreprises 1',
                    'Comptabilité approfondie',
                    'Droit des affaires',
                    'Langues étrangères 2',
                    'Communication professionnelle et outils digitaux',
                ],
                6 => [
                    'Gestion de la trésorerie',
                    'Fiscalité des entreprises 2',
                    'Intelligence artificielle et finance',
                    'Entrepreneuriat et gestion de projet',
                    'Audit comptable et financier',
                    'Langues étrangères 2',
                    'Culture entrepreneuriale',
                ],
            ],
            'Commerce et Marketing' => [
                5 => [
                    'Contrôle de gestion',
                    "Choix d'investissement et modes de financement",
                    'Fiscalité des entreprises 1',
                    'Comptabilité approfondie',
                    'Droit des affaires',
                    'Langues étrangères 2',
                    'Communication professionnelle et outils digitaux',
                ],
                6 => [
                    'Management stratégique',
                    'Entrepreneuriat et gestion de projet',
                    'Intelligence artificielle et commerce',
                    'Technique du marketing international',
                    'Marketing stratégique',
                    'Langues étrangères 2',
                    'Culture entrepreneuriale',
                ],
            ],
            'Économétrie' => [
                5 => [
                    'Microéconomie approfondie',
                    'Intelligence artificielle et recherche opérationnelle',
                    "Tests d'hypothèses",
                    'Economie internationale',
                    'Entrepreneuriat et gestion de projet',
                    'Langues étrangères 2',
                    'Initiation aux logiciels économétriques',
                ],
                6 => [
                    'Management stratégique',
                    'Entrepreneuriat et gestion de projet',
                    'Intelligence artificielle et commerce',
                    'Technique du marketing international',
                    'Marketing stratégique',
                    'Langues étrangères 2',
                    'Culture entrepreneuriale',
                ],
            ],
        ];

        $programCount = 0;
        $semesterCount = 0;
        $moduleCount = 0;

        foreach ($structure as $programName => $semesters) {
            $program = AcademicProgram::where('name', $programName)->first();

            if (! $program) {
                $this->command?->warn("Filière introuvable, ignorée : {$programName}");

                continue;
            }

            $programCount++;

            foreach ($semesters as $number => $moduleNames) {
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

                $keptNames = [];

                foreach ($moduleNames as $position => $moduleName) {
                    $keptNames[] = Str::lower(trim($moduleName));

                    Subject::query()->updateOrCreate(
                        [
                            'semester_id' => $semester->id,
                            'name' => trim($moduleName),
                        ],
                        [
                            'uuid' => (string) Str::uuid(),
                            'slug' => $this->uniqueSlug($programName.'-s'.$number.'-'.$moduleName),
                            'code' => 'S'.$number.'-M'.($position + 1),
                            'credits' => 0,
                            'coefficient' => 1,
                            'is_active' => true,
                            'sort_order' => $position,
                        ]
                    );

                    $moduleCount++;
                }

                // Retire les anciens modules génériques ("Module 1", "Module 2"...)
                // qui ne font pas partie du vrai programme, pour éviter les doublons.
                $semester->subjects()
                    ->get()
                    ->reject(fn (Subject $subject) => in_array(Str::lower(trim($subject->name)), $keptNames, true))
                    ->each(fn (Subject $subject) => $subject->delete());
            }
        }

        $this->command?->info("{$programCount} filière(s), {$semesterCount} semestre(s), {$moduleCount} module(s) synchronisés avec le vrai programme.");
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
