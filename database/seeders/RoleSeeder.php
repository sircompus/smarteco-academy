<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Administrateur',
                'description' => 'Gère l’ensemble de la plateforme et ses paramètres.',
            ],
            [
                'name' => 'superviseur',
                'display_name' => 'Superviseur',
                'description' => 'Supervise les activités pédagogiques et administratives.',
            ],
            [
                'name' => 'professeur',
                'display_name' => 'Professeur',
                'description' => 'Gère les cours, les contenus pédagogiques et le suivi des apprenants.',
            ],
            [
                'name' => 'etudiant',
                'display_name' => 'Étudiant',
                'description' => 'Accède aux cours, inscriptions, examens et services étudiants.',
            ],
            [
                'name' => 'stagiaire',
                'display_name' => 'Stagiaire',
                'description' => 'Accède aux formations pratiques et aux activités de stage.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']],
                [
                    'display_name' => $role['display_name'],
                    'description' => $role['description'],
                    'is_active' => true,
                ]
            );
        }
    }
}