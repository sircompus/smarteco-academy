<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingLesson;
use App\Models\TrainingSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(): View
    {
        $trainings = Training::query()
            ->with([
                'sessions',
                'sections.lessons',
            ])
            ->latest()
            ->get();

        return view('admin.trainings.index', [
            'trainings' => $trainings,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'short_description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'duration_minutes' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ]);

        Training::create([
            'created_by' => $request->user()->id,
            'title' => $data['title'],
            'slug' => $this->uniqueSlug($data['title']),
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? 0,
            'status' => Training::STATUS_DRAFT,
        ]);

        return back()->with(
            'success',
            'La formation a été créée comme brouillon.'
        );
    }

    public function storeSession(
        Request $request,
        Training $training
    ): RedirectResponse {
        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'code' => [
                'nullable',
                'string',
                'max:50',
            ],
            'starts_at' => [
                'required',
                'date',
            ],
            'ends_at' => [
                'nullable',
                'date',
                'after:starts_at',
            ],
            'capacity' => [
                'nullable',
                'integer',
                'min:1',
            ],
            'location' => [
                'nullable',
                'string',
                'max:255',
            ],
            'price' => [
                'required',
                'numeric',
                'min:0.01',
            ],
            'billing_type' => [
                'required',
                'in:unique,mensuel',
            ],
        ]);

        $training->sessions()->create([
            ...$data,
            'status' => 'open',
        ]);

        return back()->with(
            'success',
            'La session a été ajoutée.'
        );
    }

    public function storeSection(
        Request $request,
        Training $training
    ): RedirectResponse {
        $data = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
        ]);

        $training->sections()->create([
            ...$data,
            'is_active' => true,
            'sort_order' => $training->sections()->count() + 1,
        ]);

        return back()->with(
            'success',
            'La section a été ajoutée.'
        );
    }

    public function storeLesson(
        Request $request,
        Training $training
    ): RedirectResponse {
        $data = $request->validate([
            'training_section_id' => [
                'nullable',
                'exists:training_sections,id',
            ],
            'title' => [
                'required',
                'string',
                'max:255',
            ],
            'content' => [
                'nullable',
                'string',
            ],
            'video_url' => [
                'nullable',
                'url',
                'max:2000',
            ],
            'duration_minutes' => [
                'nullable',
                'integer',
                'min:0',
            ],
            'is_preview' => [
                'nullable',
                'boolean',
            ],
        ]);

        if (! empty($data['training_section_id'])) {
            $sectionBelongsToTraining = TrainingSection::query()
                ->where('id', $data['training_section_id'])
                ->where('training_id', $training->id)
                ->exists();

            abort_unless(
                $sectionBelongsToTraining,
                422,
                'La section ne correspond pas à cette formation.'
            );
        }

        $training->lessons()->create([
            'training_section_id' =>
                $data['training_section_id'] ?? null,
            'title' => $data['title'],
            'slug' => $this->uniqueLessonSlug($data['title']),
            'content' => $data['content'] ?? null,
            'video_url' => $data['video_url'] ?? null,
            'duration_minutes' =>
                $data['duration_minutes'] ?? 0,
            'is_preview' =>
                $request->boolean('is_preview'),
            'is_published' => true,
            'sort_order' => $training->lessons()->count() + 1,
        ]);

        return back()->with(
            'success',
            'La leçon a été ajoutée.'
        );
    }

    public function publish(
        Training $training
    ): RedirectResponse {
        if (! $training->sessions()->exists()) {
            return back()->withErrors([
                'training' =>
                    'Ajoutez au moins une session avant la publication.',
            ]);
        }

        if (
            ! $training->lessons()
                ->where('is_published', true)
                ->exists()
        ) {
            return back()->withErrors([
                'training' =>
                    'Ajoutez au moins une leçon publiée.',
            ]);
        }

        DB::transaction(function () use ($training): void {
            $training->update([
                'status' => Training::STATUS_PUBLISHED,
                'published_at' => now(),
            ]);
        });

        return back()->with(
            'success',
            'La formation a été publiée.'
        );
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $number = 2;

        while (
            Training::withTrashed()
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $number;
            $number++;
        }

        return $slug;
    }

    private function uniqueLessonSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $number = 2;

        while (
            TrainingLesson::withTrashed()
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $number;
            $number++;
        }

        return $slug;
    }
}