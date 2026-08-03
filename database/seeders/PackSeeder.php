<?php

namespace Database\Seeders;

use App\Models\Pack;
use App\Models\Semester;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PackSeeder extends Seeder
{
    /**
     * Recrée automatiquement UN SEUL pack "semestre" par semestre actif
     * (un par filière et par numéro S1...S10 réellement existant) —
     * plus de packs "module" individuels, qui créaient trop d'entrées
     * peu utiles avec des noms génériques. N'écrase jamais un pack
     * déjà existant.
     */
    public function run(): void
    {
        $defaultSemesterPrice = 250; // DH/mois, facturation mensuelle

        $semesterCount = 0;

        foreach (Semester::with('program.level')->where('is_active', true)->get() as $semester) {
            $pack = Pack::firstOrNew([
                'type' => 'semestre',
                'semester_id' => $semester->id,
            ]);

            if (! $pack->exists) {
                $pack->uuid = (string) Str::uuid();
                $pack->name = trim(sprintf(
                    '%s — %s — %s',
                    $semester->program?->level?->name,
                    $semester->program?->name,
                    $semester->name
                ), ' —');
                $pack->price = $defaultSemesterPrice;
                $pack->billing_type = 'mensuel';
                $pack->is_active = true;
                $pack->save();
                $semesterCount++;
            }
        }

        $this->command?->info("{$semesterCount} pack(s) semestre créés.");
    }
}
