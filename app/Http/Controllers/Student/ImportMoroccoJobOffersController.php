<?php

namespace App\Http\Controllers\Student;

use App\Actions\JobWatch\RunJobWatch;
use App\Http\Controllers\Controller;
use App\Http\Requests\JobWatch\ImportMoroccoJobOffersRequest;
use App\Models\JobWatch;
use App\Services\JobWatch\Importers\MoroccoCsvJobImporter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Throwable;

class ImportMoroccoJobOffersController extends Controller
{
    public function __invoke(
        ImportMoroccoJobOffersRequest $request,
        JobWatch $jobWatch,
        MoroccoCsvJobImporter $importer,
        RunJobWatch $runJobWatch
    ): RedirectResponse {
        Gate::authorize('update', $jobWatch);

        if ($jobWatch->status === 'disabled') {
            return back()->with(
                'error',
                'Cette veille est désactivée.'
            );
        }

        $file = $request->file('offers_csv');

        try {
            $importStatistics = $importer->import(
                $file->getRealPath()
            );

            $matchingStatistics = $runJobWatch->execute(
                $jobWatch
            );
        } catch (Throwable $exception) {
            report($exception);

            return back()->with(
                'error',
                'Import impossible : '.$exception->getMessage()
            );
        }

        return redirect()
            ->route('student.job-watches.show', $jobWatch)
            ->with(
                'success',
                sprintf(
                    'Import Maroc terminé : %d reçue(s), '
                    .'%d créée(s), %d mise(s) à jour, '
                    .'%d ignorée(s). Matching : %d offre(s) '
                    .'analysée(s), %d correspondance(s).',
                    $importStatistics['received'],
                    $importStatistics['created'],
                    $importStatistics['updated'],
                    $importStatistics['skipped'],
                    $matchingStatistics['analyzed'],
                    $matchingStatistics['matched']
                )
            );
    }
}
