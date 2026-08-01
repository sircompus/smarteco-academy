<?php

namespace Database\Seeders;

use App\Models\SkillSuggestion;
use Illuminate\Database\Seeder;

class SkillSuggestionSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            // --- COMPÉTENCES TECHNIQUES ET NUMÉRIQUES ---
            'Intelligence artificielle et analyse' => [
                'IA générative appliquée au management',
                "Analyse et synthèse de l'information",
                'Veille stratégique',
                'Machine learning',
                'Business intelligence',
                'Power BI',
                'R (langage statistique)',
                'Python',
            ],
            "Systèmes d'information et cloud" => [
                'Gouvernance des SI',
                'Transformation numérique',
                'Google Cloud Platform',
                'Docker',
                'Kubernetes',
                'Ansible',
                'Maven',
                'SQL Server',
                'Bases de données',
            ],
            'Développement et outils numériques' => [
                'PHP',
                'HTML',
                'JEE',
                'Outils web',
                'Word',
                'Excel',
                'Access',
                'PowerPoint',
                'Publisher',
                'MS Project',
            ],
            'Logiciels de gestion' => [
                'SAGE SAARI',
                'SAGE i7',
                'CIEL',
                'ATLAS',
                'EBP',
            ],
            'Infrastructure et maintenance' => [
                'Hardware',
                'Réseaux',
                'Électromécanique',
                'Électronique industrielle',
                'Logiciels de simulation',
            ],

            // --- COMPÉTENCES PROFESSIONNELLES ---
            'Compétences professionnelles' => [
                'Rigueur, autonomie, persévérance et sens des responsabilités',
                "Capacité d'analyse, de synthèse, d'organisation et de résolution de problèmes",
                'Pédagogie, communication, vulgarisation et accompagnement du changement',
                'Travail en équipe, relation avec les parties prenantes et orientation résultats',
            ],
        ];

        // On repart d'un catalogue propre avec cette structure.
        SkillSuggestion::query()->delete();

        $sortOrder = 0;

        foreach ($skills as $category => $names) {
            foreach ($names as $name) {
                SkillSuggestion::updateOrCreate(
                    ['name' => $name],
                    [
                        'category' => $category,
                        'is_active' => true,
                        'sort_order' => $sortOrder,
                    ]
                );

                $sortOrder++;
            }
        }
    }
}
