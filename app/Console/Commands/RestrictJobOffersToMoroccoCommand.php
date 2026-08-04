<?php

namespace App\Console\Commands;

use App\Models\JobOffer;
use App\Models\JobSource;
use Illuminate\Console\Command;

class RestrictJobOffersToMoroccoCommand extends Command
{
    protected $signature = 'job-watch:restrict-morocco
        {--force : Appliquer sans demander de confirmation}';

    protected $description = (
        'Désactive les sources internationales et archive les offres hors Maroc'
    );

    public function handle(): int
    {
        if (
            ! $this->option('force')
            && ! $this->confirm(
                'Archiver toutes les offres hors Maroc ?'
            )
        ) {
            $this->info('Opération annulée.');

            return self::SUCCESS;
        }

        $disabledSources = JobSource::query()
            ->whereNotIn('slug', [
                'morocco-csv',
                'smarteco-demo',
            ])
            ->update([
                'is_active' => false,
            ]);

        $archivedOffers = JobOffer::query()
            ->where(function ($query): void {
                $query
                    ->whereNull('country_code')
                    ->orWhere('country_code', '!=', 'MA');
            })
            ->where('status', 'active')
            ->update([
                'status' => 'archived',
            ]);

        $this->info(
            sprintf(
                '%d source(s) désactivée(s), %d offre(s) archivée(s).',
                $disabledSources,
                $archivedOffers
            )
        );

        return self::SUCCESS;
    }
}
