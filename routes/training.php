<?php

use App\Http\Controllers\Admin\TrainingController as AdminTrainingController;
use App\Http\Controllers\Admin\TrainingEnrollmentController;
use App\Http\Controllers\Student\TrainingController as StudentTrainingController;
use App\Http\Middleware\EnsureModuleIsActive;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    EnsureModuleIsActive::class . ':formations',
])->group(function (): void {
    Route::middleware([
        EnsureUserHasRole::class . ':admin',
    ])
        ->prefix('admin/formations')
        ->name('admin.trainings.')
        ->group(function (): void {
            Route::get(
                '/',
                [AdminTrainingController::class, 'index']
            )->name('index');

            Route::post(
                '/',
                [AdminTrainingController::class, 'store']
            )->name('store');

            Route::post(
                '/{training}/sessions',
                [AdminTrainingController::class, 'storeSession']
            )->name('sessions.store');

            Route::post(
                '/{training}/sections',
                [AdminTrainingController::class, 'storeSection']
            )->name('sections.store');

            Route::post(
                '/{training}/lessons',
                [AdminTrainingController::class, 'storeLesson']
            )->name('lessons.store');

            Route::patch(
                '/{training}/publish',
                [AdminTrainingController::class, 'publish']
            )->name('publish');
        });

    Route::middleware([
        EnsureUserHasRole::class . ':admin,superviseur',
    ])
        ->prefix('admin/formations')
        ->name('admin.trainings.')
        ->group(function (): void {
            Route::get(
                '/enrollments',
                [TrainingEnrollmentController::class, 'index']
            )->name('enrollments.index');

            Route::post(
                '/enrollments/{enrollment}/payments',
                [TrainingEnrollmentController::class, 'storePayment']
            )->name('enrollments.payments.store');

            Route::post(
                '/enrollments/{enrollment}/reminder',
                [TrainingEnrollmentController::class, 'sendReminder']
            )->name('enrollments.reminder');

            Route::patch(
                '/enrollments/{enrollment}/toggle-pause',
                [TrainingEnrollmentController::class, 'togglePause']
            )->name('enrollments.toggle-pause');

            Route::get(
                '/enrollments/{enrollment}/payments/{payment}/receipt',
                [TrainingEnrollmentController::class, 'receipt']
            )->name('enrollments.payments.receipt');
        });

    Route::prefix('formations')
        ->name('student.trainings.')
        ->group(function (): void {
            Route::get(
                '/',
                [StudentTrainingController::class, 'index']
            )->name('index');

            Route::get(
                '/{training}',
                [StudentTrainingController::class, 'show']
            )->name('show');

            Route::post(
                '/{training}/sessions/{session}/enroll',
                [StudentTrainingController::class, 'enroll']
            )->name('enroll');

            Route::get(
                '/{training}/lessons/{lesson}',
                [StudentTrainingController::class, 'lesson']
            )->name('lessons.show');

            Route::patch(
                '/{training}/lessons/{lesson}/complete',
                [StudentTrainingController::class, 'completeLesson']
            )->name('lessons.complete');
        });
});