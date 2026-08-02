<?php

namespace Database\Seeders;

use App\Models\AcademicLevel;
use App\Models\AcademicProgram;
use App\Models\Pack;
use App\Models\PackEnrollment;
use App\Models\PackPayment;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    /**
     * Ce seeder NE fait PAS partie de DatabaseSeeder — il ne tourne jamais
     * automatiquement via `php artisan db:seed` ou `migrate:fresh --seed`.
     * Lance-le à la demande avec :
     *   php artisan db:seed --class=DemoDataSeeder
     *
     * Il crée des étudiants de test et les inscrit à des packs déjà
     * existants dans ta base (il n'invente aucun pack/filière), avec des
     * statuts de paiement variés pour tester le suivi.
     */
    public function run(): void
    {
        $studentRole = Role::where('name', 'etudiant')->first();

        if (! $studentRole) {
            $this->command?->error('Rôle "etudiant" introuvable — lance d\'abord le RoleSeeder.');

            return;
        }

        $packs = Pack::where('is_active', true)->get();

        if ($packs->isEmpty()) {
            $this->command?->warn('Aucun pack actif trouvé — les étudiants seront créés sans inscription.');
        }

        $level = AcademicLevel::first();
        $program = AcademicProgram::first();

        $students = [
            ['name' => 'Yassine Amrani', 'email' => 'demo.yassine@smarteco.test', 'payment' => 'complet'],
            ['name' => 'Salma Benjelloun', 'email' => 'demo.salma@smarteco.test', 'payment' => 'partiel'],
            ['name' => 'Omar Tahiri', 'email' => 'demo.omar@smarteco.test', 'payment' => 'aucun'],
            ['name' => 'Imane El Fassi', 'email' => 'demo.imane@smarteco.test', 'payment' => 'complet'],
            ['name' => 'Karim Ziani', 'email' => 'demo.karim@smarteco.test', 'payment' => 'aucun'],
            ['name' => 'Nour Bouzid', 'email' => 'demo.nour@smarteco.test', 'payment' => 'en_attente'],
        ];

        $registrationStatuses = [
            Registration::STATUS_SUBMITTED,
            Registration::STATUS_UNDER_REVIEW,
            Registration::STATUS_ACCEPTED,
            Registration::STATUS_ACCEPTED,
            Registration::STATUS_REJECTED,
            Registration::STATUS_SUBMITTED,
        ];

        foreach ($students as $index => $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            if (! $user->roles()->where('roles.id', $studentRole->id)->exists()) {
                $user->roles()->attach($studentRole->id);
            }

            // --- Dossier d'admission (module Registration) ---
            if ($level && $program) {
                Registration::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'academic_level_id' => $level->id,
                        'academic_program_id' => $program->id,
                        'academic_year' => '2026-2027',
                        'status' => $registrationStatuses[$index],
                        'first_name' => explode(' ', $data['name'])[0],
                        'last_name' => explode(' ', $data['name'])[1] ?? '',
                        'phone' => '06' . rand(10000000, 99999999),
                        'city' => 'Tétouan',
                        'country' => 'Maroc',
                        'submitted_at' => now()->subDays(rand(2, 20)),
                    ]
                );
            }

            // --- Inscription à un pack + paiement ---
            if ($packs->isNotEmpty()) {
                $pack = $packs->random();

                $enrollment = PackEnrollment::updateOrCreate(
                    ['user_id' => $user->id, 'pack_id' => $pack->id],
                    [
                        'uuid' => (string) Str::uuid(),
                        'amount_due' => $pack->price,
                        'status' => $data['payment'] === 'en_attente' ? 'en_attente' : 'active',
                        'activated_at' => $data['payment'] === 'en_attente' ? null : now()->subDays(rand(1, 15)),
                    ]
                );

                // Repart de zéro sur les versements pour éviter les doublons si on relance le seeder.
                $enrollment->payments()->delete();

                match ($data['payment']) {
                    'complet' => PackPayment::create([
                        'uuid' => (string) Str::uuid(),
                        'pack_enrollment_id' => $enrollment->id,
                        'amount' => $pack->price,
                        'paid_at' => now()->subDays(rand(1, 10)),
                        'note' => 'Paiement intégral (démo)',
                    ]),
                    'partiel' => PackPayment::create([
                        'uuid' => (string) Str::uuid(),
                        'pack_enrollment_id' => $enrollment->id,
                        'amount' => round($pack->price / 2, 2),
                        'paid_at' => now()->subDays(rand(1, 10)),
                        'note' => '1ère tranche (démo)',
                    ]),
                    default => null, // 'aucun' et 'en_attente' : pas de versement
                };
            }
        }

        $this->command?->info('Données de démonstration créées : ' . count($students) . ' étudiants (mot de passe commun : "password").');
    }
}
