<?php

namespace Database\Seeders;

use App\Models\Pack;
use App\Models\Semester;
use App\Models\Subject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PackSeeder extends Seeder
{
    /**
     * Recrée automatiquement un pack "semestre" par semestre actif et un
     * pack "module" par matière active — même logique que le bouton
     * "Générer les packs" de l'admin (PackController::generate).
     * N'écrase jamais un pack déjà existant.
     */
    public function run(): void
    {
        $defaultSemesterPrice = 250; // DH/mois, facturation mensuelle
        $defaultModulePrice = 100;   // DH, prix unique

        $semesterCount = 0;
        $moduleCount = 0;

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

        foreach (Subject::with('semester.program.level')->where('is_active', true)->get() as $subject) {
            $pack = Pack::firstOrNew([
                'type' => 'module',
                'subject_id' => $subject->id,
            ]);

            if (! $pack->exists) {
                $pack->uuid = (string) Str::uuid();
                $pack->name = trim(sprintf(
                    '%s — %s — %s',
                    $subject->semester?->program?->level?->name,
                    $subject->semester?->name,
                    $subject->name
                ), ' —');
                $pack->price = $defaultModulePrice;
                $pack->billing_type = 'unique';
                $pack->is_active = true;
                $pack->save();
                $moduleCount++;
            }
        }

        $this->command?->info("{$semesterCount} pack(s) semestre et {$moduleCount} pack(s) module créés.");
    }
}
