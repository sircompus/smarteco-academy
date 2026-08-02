<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            [
                'name' => 'Centre',
                'slug' => 'centre',
                'description' => 'Gestion académique et administrative du centre.',
                'route_prefix' => 'centre',
                'menu_order' => 1,
                'is_core' => true,
            ],
            [
                'name' => 'Inscription',
                'slug' => 'inscription',
                'description' => 'Gestion des inscriptions et des dossiers des apprenants.',
                'route_prefix' => 'inscription',
                'menu_order' => 2,
                'is_core' => true,
            ],
            [
                'name' => 'Informatique et IA',
                'slug' => 'informatique-ia',
                'description' => 'Cours, formations et services liés à l’informatique et à l’intelligence artificielle.',
                'route_prefix' => 'informatique-ia',
                'menu_order' => 3,
                'is_core' => false,
            ],
            [
                'name' => 'Examens',
                'slug' => 'examens',
                'description' => 'Préparation, organisation et suivi des examens.',
                'route_prefix' => 'examens',
                'menu_order' => 4,
                'is_core' => false,
            ],
            [
                'name' => 'Formations',
                'slug' => 'formations',
                'description' => 'Gestion des formations pratiques et certifiantes.',
                'route_prefix' => 'formations',
                'menu_order' => 5,
                'is_core' => false,
            ],
            [
                'name' => 'Projets',
                'slug' => 'projets',
                'description' => 'Gestion des PFE, mémoires, thèses, exposés et projets académiques.',
                'route_prefix' => 'projets',
                'menu_order' => 6,
                'is_core' => false,
            ],
            [
                'name' => 'Services',
                'slug' => 'services',
                'description' => 'Services complémentaires proposés par la plateforme.',
                'route_prefix' => 'services',
                'menu_order' => 7,
                'is_core' => false,
            ],
            [
                'name' => 'CV ATS',
                'slug' => 'cv-ats',
                'description' => 'Création et optimisation de CV compatibles avec les systèmes ATS.',
                'route_prefix' => 'cv-ats',
                'menu_order' => 8,
                'is_core' => false,
            ],
            [
                'name' => 'Portfolio',
                'slug' => 'portfolio',
                'description' => 'Création et gestion des portfolios professionnels.',
                'route_prefix' => 'portfolio',
                'menu_order' => 9,
                'is_core' => false,
            ],
            [
                'name' => 'Community',
                'slug' => 'community',
                'description' => 'Espace communautaire destiné aux étudiants et aux formateurs.',
                'route_prefix' => 'community',
                'menu_order' => 10,
                'is_core' => false,
            ],
            [
                'name' => 'Divertissement',
                'slug' => 'divertissement',
                'description' => 'Contenus et activités de divertissement.',
                'route_prefix' => 'divertissement',
                'menu_order' => 11,
                'is_core' => false,
            ],
        ];

        foreach ($modules as $module) {
            Module::updateOrCreate(
                [
                    'slug' => $module['slug'],
                ],
                [
                    'name' => $module['name'],
                    'description' => $module['description'],
                    'version' => '1.0.0',
                    'icon' => null,
                    'route_prefix' => $module['route_prefix'],
                    'is_active' => true,
                    'is_core' => $module['is_core'],
                    'menu_order' => $module['menu_order'],
                ]
            );
        }
    }
}