<?php

namespace Database\Seeders;

use App\Models\SkillSuggestion;
use Illuminate\Database\Seeder;

class SkillSuggestionSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            'Gestion' => [
                'Excel avancé', 'Word', 'PowerPoint', 'Sage Comptabilité', 'SAP',
                'Comptabilité générale', 'Comptabilité analytique', 'Fiscalité',
                'Contrôle de gestion', 'Analyse financière', 'Gestion budgétaire', 'Audit',
            ],
            'Commerce' => [
                'Marketing digital', 'Négociation commerciale', 'Relation client',
                'Étude de marché', 'Réseaux sociaux', 'SEO / référencement',
            ],
            'Ressources humaines' => [
                'Gestion de la paie', 'Recrutement', 'Droit du travail', 'Gestion des conflits',
            ],
            'Économétrie / Data' => [
                'Statistiques', 'Analyse de données', 'Python', 'R (langage statistique)',
                'SPSS', 'Économétrie appliquée',
            ],
            'Transversal' => [
                'Gestion de projet', "Travail d'équipe", 'Communication',
                'Rédaction professionnelle', 'Intelligence artificielle (bases)', 'Anglais des affaires',
            ],
        ];

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
