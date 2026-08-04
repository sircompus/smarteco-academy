<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Veille d’emploi — Maroc uniquement
|--------------------------------------------------------------------------
|
| Aucun import international n’est planifié ici.
| Les offres marocaines sont importées avec :
|
| php artisan job-watch:import-morocco-csv
|     storage/app/imports/offres-maroc.csv
|     --match
|
| Une API ou un flux partenaire marocain autorisé pourra être planifié
| ultérieurement.
|
*/
