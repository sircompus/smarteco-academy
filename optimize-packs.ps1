$path0 = "C:\laragon\www\SEA\database\seeders\PackSeeder.php"
$content0 = @'
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

'@
$dir0 = Split-Path $path0 -Parent
if (-not (Test-Path $dir0)) { New-Item -ItemType Directory -Path $dir0 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: database/seeders/PackSeeder.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: database/seeders/PackSeeder.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\app\Console\Commands\CleanupModulePacks.php"
$content1 = @'
<?php

namespace App\Console\Commands;

use App\Models\Pack;
use Illuminate\Console\Command;

class CleanupModulePacks extends Command
{
    protected $signature = 'packs:cleanup-modules';

    protected $description = "Supprime les packs de type 'module' qui n'ont aucune inscription (nettoyage après génération automatique) — les packs semestre ne sont jamais touchés.";

    public function handle(): int
    {
        $packs = Pack::where('type', 'module')
            ->withCount('enrollments')
            ->get()
            ->filter(fn (Pack $pack) => $pack->enrollments_count === 0);

        if ($packs->isEmpty()) {
            $this->info("Aucun pack module a supprimer (soit il n'y en a pas, soit ils ont tous des inscriptions).");

            return self::SUCCESS;
        }

        $this->info("{$packs->count()} pack(s) module sans inscription vont etre supprimes :");

        foreach ($packs as $pack) {
            $this->line("- {$pack->name}");
        }

        foreach ($packs as $pack) {
            $pack->delete();
        }

        $this->info('Nettoyage termine.');

        return self::SUCCESS;
    }
}

'@
$dir1 = Split-Path $path1 -Parent
if (-not (Test-Path $dir1)) { New-Item -ItemType Directory -Path $dir1 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: app/Console/Commands/CleanupModulePacks.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: app/Console/Commands/CleanupModulePacks.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "Termine." -ForegroundColor Cyan
