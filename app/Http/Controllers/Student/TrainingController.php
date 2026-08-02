<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingEnrollment;
use App\Models\TrainingLesson;
use App\Models\TrainingProgress;
use App\Models\TrainingSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TrainingController extends Controller
{
    public function index(): View
    {
        $trainings = Training::query()
            ->published()
            ->withCount([
                'lessons',
                'sessions',
            ])
            ->orderByDesc('published_at')
            ->paginate(12);

        return view('student.trainings.index', [
            'trainings' => $trainings,
        ]);
    }

    public function show(
        Request $request,
        Training $training
    ): View {
        $this->ensurePublished($training);

        $training->load([
            'sessions',
            'sections.lessons' => function ($query) {
                $query->where('is_published', true)
                    ->orderBy('sort_order');
            },
        ]);

        $enrollment = $training
            ->enrollments()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', [
                'active',
                'completed',
            ])
            ->latest()
            ->first();

        $progressByLesson = $enrollment
            ? $enrollment->progress()
                ->pluck('status', 'training_lesson_id')
            : collect();

        return view('student.trainings.show', [
            'training' => $training,
            'enrollment' => $enrollment,
            'progressByLesson' => $progressByLesson,
        ]);
    }

    public function enroll(
        Request $request,
        Training $training,
        TrainingSession $session
    ): RedirectResponse {
        $this->ensurePublished($training);

        abort_unless(
            $session->training_id === $training->id,
            404
        );

        if (! $session->isOpenForEnrollment()) {
            return back()->withErrors([
                'session' =>
                    'Cette session n’accepte actuellement aucune inscription.',
            ]);
        }

        $existing = TrainingEnrollment::query()
            ->where('training_session_id', $session->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        if ($existing) {
            return back()->withErrors([
                'session' =>
                    'Vous êtes déjà inscrit à cette session.',
            ]);
        }

        TrainingEnrollment::create([
            'uuid' => (string) \Illuminate\Support\Str::uuid(),
            'training_id' => $training->id,
            'training_session_id' => $session->id,
            'user_id' => $request->user()->id,
            'status' => 'active',
            'amount_due' => $session->price,
            'progress_percentage' => 0,
            'enrolled_at' => now(),
        ]);

        return back()->with(
            'success',
            'Votre inscription à la formation a été enregistrée.'
        );
    }

    public function lesson(
        Request $request,
        Training $training,
        TrainingLesson $lesson
    ): View {
        $this->ensurePublished($training);

        abort_unless(
            $lesson->training_id === $training->id
            && $lesson->is_published,
            404
        );

        $enrollment = $this->findEnrollment(
            $request,
            $training
        );

        $progress = TrainingProgress::updateOrCreate(
            [
                'training_enrollment_id' => $enrollment->id,
                'training_lesson_id' => $lesson->id,
            ],
            [
                'status' => 'in_progress',
                'progress_percentage' => 0,
                'started_at' => now(),
                'last_accessed_at' => now(),
            ]
        );

        return view('student.trainings.lesson', [
            'training' => $training,
            'lesson' => $lesson,
            'enrollment' => $enrollment,
            'progress' => $progress,
        ]);
    }

    public function completeLesson(
        Request $request,
        Training $training,
        TrainingLesson $lesson
    ): RedirectResponse {
        $this->ensurePublished($training);

        abort_unless(
            $lesson->training_id === $training->id
            && $lesson->is_published,
            404
        );

        $enrollment = $this->findEnrollment(
            $request,
            $training
        );

        DB::transaction(function () use (
            $enrollment,
            $lesson,
            $training
        ): void {
            TrainingProgress::updateOrCreate(
                [
                    'training_enrollment_id' => $enrollment->id,
                    'training_lesson_id' => $lesson->id,
                ],
                [
                    'status' => 'completed',
                    'progress_percentage' => 100,
                    'started_at' => now(),
                    'last_accessed_at' => now(),
                    'completed_at' => now(),
                ]
            );

            $totalLessons = $training
                ->lessons()
                ->where('is_published', true)
                ->count();

            $completedLessons = $enrollment
                ->progress()
                ->where('status', 'completed')
                ->whereHas('lesson', function ($query) {
                    $query->where('is_published', true);
                })
                ->count();

            $percentage = $totalLessons > 0
                ? round(
                    ($completedLessons / $totalLessons) * 100,
                    2
                )
                : 0;

            $enrollment->update([
                'progress_percentage' => $percentage,
                'status' => $percentage >= 100
                    ? 'completed'
                    : 'active',
                'completed_at' => $percentage >= 100
                    ? now()
                    : null,
            ]);
        });

        return redirect()
            ->route('student.trainings.show', $training)
            ->with(
                'success',
                'La progression a été enregistrée.'
            );
    }

    private function findEnrollment(
        Request $request,
        Training $training
    ): TrainingEnrollment {
        return $training
            ->enrollments()
            ->where('user_id', $request->user()->id)
            ->whereIn('status', [
                'active',
                'completed',
            ])
            ->latest()
            ->firstOrFail();
    }

    private function ensurePublished(
        Training $training
    ): void {
        abort_unless(
            $training->status === Training::STATUS_PUBLISHED
            && $training->published_at !== null,
            404
        );
    }
}