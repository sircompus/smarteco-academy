<?php

namespace Database\Seeders;

use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\TrainingPayment;
use App\Models\TrainingSession;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TrainingDemoSeeder extends Seeder
{
    /**
     * Crée 3 formations de démonstration avec sessions, étudiants et
     * paiements variés (soldé, partiel, impayé), pour retester le module
     * Formations courtes sans attendre de vraies inscriptions.
     */
    public function run(): void
    {
        $admin = User::whereRaw('LOWER(email) = ?', ['sircompus@gmail.com'])->first();

        $trainings = [
            [
                'title' => 'Excel avancé pour la gestion',
                'short_description' => 'Tableaux croisés dynamiques, formules avancées, automatisation.',
                'price' => 400,
                'billing_type' => 'unique',
            ],
            [
                'title' => 'Initiation à Power BI',
                'short_description' => 'Créer des tableaux de bord et visualiser ses données.',
                'price' => 150,
                'billing_type' => 'mensuel',
            ],
            [
                'title' => "Préparation à l'entretien d'embauche",
                'short_description' => "Techniques d'entretien, simulation, CV et lettre de motivation.",
                'price' => 250,
                'billing_type' => 'unique',
            ],
        ];

        $students = [
            ['name' => 'Yassine Demo Formation', 'email' => 'demo.formation.yassine@smarteco.test', 'paid' => 'complet'],
            ['name' => 'Salma Demo Formation', 'email' => 'demo.formation.salma@smarteco.test', 'paid' => 'partiel'],
            ['name' => 'Omar Demo Formation', 'email' => 'demo.formation.omar@smarteco.test', 'paid' => 'aucun'],
        ];

        $studentUsers = collect($students)->map(function (array $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $role = \App\Models\Role::where('name', 'etudiant')->first();

            if ($role && ! $user->roles()->where('roles.id', $role->id)->exists()) {
                $user->roles()->attach($role->id);
            }

            return ['user' => $user, 'paid' => $data['paid']];
        });

        foreach ($trainings as $trainingData) {
            $training = Training::firstOrCreate(
                ['slug' => Str::slug($trainingData['title'])],
                [
                    'uuid' => (string) Str::uuid(),
                    'created_by' => $admin?->id,
                    'title' => $trainingData['title'],
                    'short_description' => $trainingData['short_description'],
                    'status' => 'published',
                    'published_at' => now(),
                    'is_featured' => false,
                ]
            );

            $session = TrainingSession::firstOrCreate(
                ['training_id' => $training->id, 'code' => 'DEMO-01'],
                [
                    'uuid' => (string) Str::uuid(),
                    'trainer_id' => $admin?->id,
                    'title' => $trainingData['title'].' — Session démo',
                    'status' => 'open',
                    'starts_at' => now()->subMonths(1),
                    'ends_at' => now()->addMonths(2),
                    'capacity' => 20,
                    'price' => $trainingData['price'],
                    'billing_type' => $trainingData['billing_type'],
                ]
            );

            foreach ($studentUsers as $studentData) {
                $enrollment = TrainingEnrollment::firstOrCreate(
                    [
                        'training_session_id' => $session->id,
                        'user_id' => $studentData['user']->id,
                    ],
                    [
                        'uuid' => (string) Str::uuid(),
                        'training_id' => $training->id,
                        'status' => 'active',
                        'amount_due' => $trainingData['price'],
                        'enrolled_at' => now()->subWeeks(3),
                    ]
                );

                if ($enrollment->payments()->exists()) {
                    continue; // déjà des paiements, ne pas dupliquer
                }

                match ($studentData['paid']) {
                    'complet' => TrainingPayment::create([
                        'uuid' => (string) Str::uuid(),
                        'training_enrollment_id' => $enrollment->id,
                        'recorded_by' => $admin?->id,
                        'amount' => $trainingData['price'],
                        'paid_at' => now()->subWeeks(2),
                        'note' => 'Paiement complet (démo)',
                    ]),
                    'partiel' => TrainingPayment::create([
                        'uuid' => (string) Str::uuid(),
                        'training_enrollment_id' => $enrollment->id,
                        'recorded_by' => $admin?->id,
                        'amount' => round($trainingData['price'] / 2, 2),
                        'paid_at' => now()->subWeeks(1),
                        'note' => '1ère tranche (démo)',
                    ]),
                    default => null, // aucun paiement
                };
            }
        }

        $this->command?->info('Formations, sessions, inscriptions et paiements de démo créés.');
        $this->command?->info('Comptes étudiants démo (mot de passe: password) : '.$studentUsers->pluck('user.email')->implode(', '));
    }
}
