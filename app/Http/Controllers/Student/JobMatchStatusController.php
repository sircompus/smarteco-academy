<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\JobMatch;
use App\Models\JobWatch;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class JobMatchStatusController extends Controller
{
    public function __invoke(
        Request $request,
        JobWatch $jobWatch,
        JobMatch $jobMatch
    ): RedirectResponse {
        Gate::authorize('update', $jobWatch);

        abort_unless(
            (int) $jobMatch->job_watch_id === (int) $jobWatch->getKey(),
            404
        );

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'new',
                    'viewed',
                    'saved',
                    'dismissed',
                    'applied',
                ]),
            ],
        ]);

        $status = $validated['status'];
        $attributes = ['status' => $status];

        if ($status === 'viewed' && $jobMatch->viewed_at === null) {
            $attributes['viewed_at'] = now();
        }

        if ($status === 'saved' && $jobMatch->saved_at === null) {
            $attributes['saved_at'] = now();
        }

        if ($status === 'applied' && $jobMatch->applied_at === null) {
            $attributes['applied_at'] = now();
        }

        $jobMatch->update($attributes);

        return back()->with(
            'success',
            match ($status) {
                'viewed' => 'L’offre a été marquée comme consultée.',
                'saved' => 'L’offre a été enregistrée.',
                'dismissed' => 'L’offre a été ignorée.',
                'applied' => 'La candidature a été enregistrée.',
                default => 'Le statut a été réinitialisé.',
            }
        );
    }
}
