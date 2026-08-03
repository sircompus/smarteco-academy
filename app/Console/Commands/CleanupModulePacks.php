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
