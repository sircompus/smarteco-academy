<?php

use App\Http\Controllers\Student\JobWatchController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])
    ->prefix('job-watches')
    ->name('student.job-watches.')
    ->group(function (): void {
        Route::get('/', [JobWatchController::class, 'index'])
            ->name('index');

        Route::get('/create', [JobWatchController::class, 'create'])
            ->name('create');

        Route::post('/', [JobWatchController::class, 'store'])
            ->name('store');

        Route::get('/{jobWatch}', [JobWatchController::class, 'show'])
            ->name('show');

        Route::get('/{jobWatch}/edit', [JobWatchController::class, 'edit'])
            ->name('edit');

        Route::match(['put', 'patch'], '/{jobWatch}', [
            JobWatchController::class,
            'update',
        ])->name('update');

        Route::patch('/{jobWatch}/status', [
            JobWatchController::class,
            'toggleStatus',
        ])->name('status');

        Route::delete('/{jobWatch}', [
            JobWatchController::class,
            'destroy',
        ])->name('destroy');
    });
