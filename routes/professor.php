<?php

use App\Http\Controllers\Professor\CourseController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:professeur',
])
    ->prefix('professeur')
    ->name('professor.')
    ->group(function () {
        Route::get('/dashboard', [CourseController::class, 'index'])
            ->name('dashboard');

        Route::get('/courses/{course}', [CourseController::class, 'show'])
            ->name('courses.show');

        Route::post('/courses/{course}/sections', [CourseController::class, 'storeSection'])
            ->name('courses.sections.store');

        Route::post('/courses/{course}/lessons', [CourseController::class, 'storeLesson'])
            ->name('courses.lessons.store');

        Route::patch('/lessons/{lesson}/toggle-publish', [CourseController::class, 'togglePublishLesson'])
            ->name('lessons.toggle-publish');

        Route::delete('/lessons/{lesson}', [CourseController::class, 'destroyLesson'])
            ->name('lessons.destroy');

        Route::post('/courses/{course}/resources', [CourseController::class, 'storeResource'])
            ->name('courses.resources.store');

        Route::delete('/resources/{resource}', [CourseController::class, 'destroyResource'])
            ->name('resources.destroy');
    });
