<?php

namespace Database\Seeders;

use App\Models\Pack;
use App\Models\PackEnrollment;
use App\Models\PackPayment;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MonthlyBillingDemoSeeder extends Seeder
{
    /**
     * Se lance à la demande :
     *   php artisan db:seed --class=MonthlyBillingDemoSeeder
     *
     * Utilise un pack déjà existant dans ta base (le premier pack de type
     * "semestre" trouvé), le bascule en facturation mensuelle, et crée
     * 3 étudiants de test dans des situations différentes pour vérifier
     * le cumul, les versements et la pause.
     */
    public function run(): void
    {
        $studentRole = Role::where('name', 'etudiant')->first();

        if (! $studentRole) {
            $this->command?->error('Rôle "etudiant" introuvable.');

            return;
        }

        $pack = Pack::where('type', 'semestre')->first();

        if (! $pack) {
            $this->command?->error('Aucun pack trouvé — crée au moins un pack avant de lancer ce seeder.');

            return;
        }

        // Bascule ce pack en facturation mensuelle pour le test.
        $pack->update(['billing_type' => 'mensuel']);

        $this->command?->info("Pack utilisé pour le test : « {$pack->name} » (désormais en facturation mensuelle, {$pack->price} DH/mois).");

        $scenarios = [
            [
                'name' => 'Test Mensuel Sans Pause',
                'email' => 'demo.mensuel1@smarteco.test',
                'months_ago' => 3,
                'paused' => false,
                'payment' => null,
            ],
            [
                'name' => 'Test Mensuel Avec Pause',
                'email' => 'demo.mensuel2@smarteco.test',
                'months_ago' => 3,
                'paused' => true, // en pause depuis 1 mois
                'payment' => null,
            ],
            [
                'name' => 'Test Mensuel Paiement Partiel',
                'email' => 'demo.mensuel3@smarteco.test',
                'months_ago' => 4,
                'paused' => false,
                'payment' => 'partiel',
            ],
        ];

        foreach ($scenarios as $data) {
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

            $activatedAt = now()->subMonths($data['months_ago']);

            $enrollment = PackEnrollment::updateOrCreate(
                ['user_id' => $user->id, 'pack_id' => $pack->id],
                [
                    'uuid' => (string) Str::uuid(),
                    'amount_due' => $pack->price,
                    'status' => 'active',
                    'activated_at' => $activatedAt,
                    'paused_at' => $data['paused'] ? now()->subMonth() : null,
                    'paused_days' => 0,
                ]
            );

            $enrollment->payments()->delete();

            if ($data['payment'] === 'partiel') {
                PackPayment::create([
                    'uuid' => (string) Str::uuid(),
                    'pack_enrollment_id' => $enrollment->id,
                    'amount' => round($pack->price / 2, 2),
                    'paid_at' => now()->subMonths(1),
                    'note' => 'Versement de test (démo)',
                ]);
            }

            $this->command?->info(
                "→ {$data['name']} ({$data['email']} / password) : inscrit il y a {$data['months_ago']} mois"
                . ($data['paused'] ? ', en pause depuis 1 mois' : '')
                . ($data['payment'] ? ', avec un versement partiel' : ', aucun versement')
            );
        }
    }
}
