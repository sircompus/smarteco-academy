<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobWatch\StoreJobWatchRequest;
use App\Http\Requests\JobWatch\UpdateJobWatchRequest;
use App\Models\CvProfile;
use App\Models\JobWatch;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\View\View;

class JobWatchController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', JobWatch::class);

        $jobWatches = JobWatch::query()
            ->where('user_id', $request->user()->id)
            ->with('cvProfile')
            ->withCount([
                'keywords',
                'matches',
            ])
            ->latest()
            ->paginate(10);

        return view('student.job-watches.index', [
            'jobWatches' => $jobWatches,
        ]);
    }

    public function create(Request $request): View
    {
        Gate::authorize('create', JobWatch::class);

        return view('student.job-watches.create', [
            'cvProfiles' => $this->cvProfilesForUser(
                (int) $request->user()->id
            ),
        ]);
    }

    public function store(
        StoreJobWatchRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $jobWatch = DB::transaction(function () use (
            $request,
            $validated
        ): JobWatch {
            $jobWatch = JobWatch::query()->create(
                $this->watchAttributes(
                    $validated,
                    (int) $request->user()->id
                )
            );

            $this->replaceKeywords(
                $jobWatch,
                $validated['keywords'] ?? []
            );

            return $jobWatch;
        });

        return redirect()
            ->route('student.job-watches.show', $jobWatch)
            ->with(
                'success',
                'La veille d’emploi a été créée avec succès.'
            );
    }

    public function show(JobWatch $jobWatch): View
    {
        Gate::authorize('view', $jobWatch);

        $jobWatch->load([
            'cvProfile',
            'keywords',
            'matches.jobOffer.source',
        ]);

        return view('student.job-watches.show', [
            'jobWatch' => $jobWatch,
        ]);
    }

    public function edit(
        Request $request,
        JobWatch $jobWatch
    ): View {
        Gate::authorize('update', $jobWatch);

        $jobWatch->load('keywords');

        return view('student.job-watches.edit', [
            'jobWatch' => $jobWatch,
            'cvProfiles' => $this->cvProfilesForUser(
                (int) $request->user()->id
            ),
        ]);
    }

    public function update(
        UpdateJobWatchRequest $request,
        JobWatch $jobWatch
    ): RedirectResponse {
        Gate::authorize('update', $jobWatch);

        $validated = $request->validated();

        DB::transaction(function () use (
            $validated,
            $jobWatch
        ): void {
            $jobWatch->update(
                $this->watchAttributes(
                    $validated,
                    (int) $jobWatch->user_id,
                    $jobWatch
                )
            );

            $this->replaceKeywords(
                $jobWatch,
                $validated['keywords'] ?? []
            );
        });

        return redirect()
            ->route('student.job-watches.show', $jobWatch)
            ->with(
                'success',
                'La veille d’emploi a été mise à jour.'
            );
    }

    public function toggleStatus(
        JobWatch $jobWatch
    ): RedirectResponse {
        Gate::authorize('update', $jobWatch);

        if ($jobWatch->status === 'disabled') {
            return back()->with(
                'error',
                'Cette veille a été désactivée par le système.'
            );
        }

        $newStatus = $jobWatch->status === 'active'
            ? 'paused'
            : 'active';

        $jobWatch->update([
            'status' => $newStatus,
            'next_run_at' => $newStatus === 'active'
                ? now()->addMinutes(
                    (int) $jobWatch->frequency_minutes
                )
                : null,
        ]);

        $message = $newStatus === 'active'
            ? 'La veille a été réactivée.'
            : 'La veille a été suspendue.';

        return back()->with('success', $message);
    }

    public function destroy(
        JobWatch $jobWatch
    ): RedirectResponse {
        Gate::authorize('delete', $jobWatch);

        DB::transaction(function () use ($jobWatch): void {
            $jobWatch->delete();
        });

        return redirect()
            ->route('student.job-watches.index')
            ->with(
                'success',
                'La veille d’emploi a été supprimée.'
            );
    }

    private function cvProfilesForUser(int $userId): Collection
    {
        return CvProfile::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->get();
    }

    private function watchAttributes(
        array $validated,
        int $userId,
        ?JobWatch $existingJobWatch = null
    ): array {
        $status = $validated['status']
            ?? $existingJobWatch?->status
            ?? 'active';

        $frequency = (int) $validated['frequency_minutes'];

        $attributes = Arr::except($validated, [
            'keywords',
        ]);

        $attributes['user_id'] = $userId;

        $attributes['target_titles'] = $this->cleanList(
            $validated['target_titles'] ?? []
        );

        $attributes['preferred_locations'] = $this->cleanList(
            $validated['preferred_locations'] ?? []
        );

        $attributes['contract_types'] = array_values(
            array_unique(
                $validated['contract_types'] ?? []
            )
        );

        $attributes['status'] = $status;

        $attributes['next_run_at'] = $status === 'active'
            ? now()->addMinutes($frequency)
            : null;

        return $attributes;
    }

    private function replaceKeywords(
        JobWatch $jobWatch,
        array $keywords
    ): void {
        $preparedKeywords = collect($keywords)
            ->map(function (array $item): array {
                $keyword = trim(
                    (string) ($item['keyword'] ?? '')
                );

                return [
                    'keyword' => $keyword,
                    'normalized_keyword' => Str::lower(
                        Str::ascii($keyword)
                    ),
                    'type' => $item['type'] ?? 'include',
                    'weight' => (int) ($item['weight'] ?? 1),
                ];
            })
            ->filter(
                fn (array $item): bool => $item['normalized_keyword'] !== ''
            )
            ->unique(
                fn (array $item): string => $item['type'].'|'.$item['normalized_keyword']
            )
            ->values()
            ->all();

        $jobWatch->keywords()->delete();

        if ($preparedKeywords !== []) {
            $jobWatch->keywords()->createMany(
                $preparedKeywords
            );
        }
    }

    private function cleanList(array $values): array
    {
        return collect($values)
            ->map(
                fn ($value): string => trim((string) $value)
            )
            ->filter()
            ->unique(
                fn (string $value): string => Str::lower(Str::ascii($value))
            )
            ->values()
            ->all();
    }
}
