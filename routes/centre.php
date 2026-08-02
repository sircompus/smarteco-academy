<?php

use App\Http\Controllers\Admin\AcademicResourceController;
use App\Http\Controllers\Admin\CentreController;
use App\Http\Controllers\Admin\CourseContentController;
use App\Http\Controllers\Admin\CurriculumController;
use App\Http\Controllers\Admin\PackController as AdminPackController;
use App\Http\Controllers\Admin\PackEnrollmentController;
use App\Http\Controllers\Admin\PaymentReportController;
use App\Http\Controllers\Student\CourseController;
use App\Http\Controllers\Student\PackController as StudentPackController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:admin',
    'module.active:centre',
])
    ->prefix('admin/centre')
    ->name('admin.centre.')
    ->group(function () {
        Route::get('/', [CentreController::class, 'index'])
            ->name('index');

        Route::post('/levels', [CentreController::class, 'storeLevel'])
            ->name('levels.store');

        Route::post('/programs', [CentreController::class, 'storeProgram'])
            ->name('programs.store');

        Route::post('/semesters', [CentreController::class, 'storeSemester'])
            ->name('semesters.store');

        Route::post('/subjects', [CentreController::class, 'storeSubject'])
            ->name('subjects.store');

        Route::post('/courses', [CentreController::class, 'storeCourse'])
            ->name('courses.store');

        Route::patch(
            '/courses/{course}/publish',
            [CentreController::class, 'publishCourse']
        )->name('courses.publish');

        Route::get('/packs', [AdminPackController::class, 'index'])
            ->name('packs.index');

        Route::post('/packs', [AdminPackController::class, 'store'])
            ->name('packs.store');

        Route::post('/packs/generate', [AdminPackController::class, 'generate'])
            ->name('packs.generate');

        Route::get('/packs/{pack}/edit', [AdminPackController::class, 'edit'])
            ->name('packs.edit');

        Route::patch('/packs/{pack}', [AdminPackController::class, 'update'])
            ->name('packs.update');

        Route::delete('/packs/{pack}', [AdminPackController::class, 'destroy'])
            ->name('packs.destroy');

        Route::delete('/packs', [AdminPackController::class, 'destroyBulk'])
            ->name('packs.destroy-bulk');

        Route::get('/curriculum', [CurriculumController::class, 'index'])
            ->name('curriculum.index');

        Route::post('/curriculum', [CurriculumController::class, 'sync'])
            ->name('curriculum.sync');

        Route::delete('/curriculum/programs/{program}', [CurriculumController::class, 'destroyProgram'])
            ->name('curriculum.programs.destroy');

        Route::get('/courses/{course}/content', [CourseContentController::class, 'edit'])
            ->name('courses.content');

        Route::post('/courses/{course}/sections', [CourseContentController::class, 'storeSection'])
            ->name('courses.sections.store');

        Route::post('/courses/{course}/lessons', [CourseContentController::class, 'storeLesson'])
            ->name('courses.lessons.store');

        Route::patch('/lessons/{lesson}/toggle-publish', [CourseContentController::class, 'togglePublishLesson'])
            ->name('lessons.toggle-publish');

        Route::delete('/lessons/{lesson}', [CourseContentController::class, 'destroyLesson'])
            ->name('lessons.destroy');

        Route::post('/courses/{course}/resources', [CourseContentController::class, 'storeResource'])
            ->name('courses.resources.store');

        Route::delete('/resources/{resource}', [CourseContentController::class, 'destroyResource'])
            ->name('resources.destroy');

        Route::get('/library', [AcademicResourceController::class, 'index'])
            ->name('library.index');

        Route::post('/library', [AcademicResourceController::class, 'store'])
            ->name('library.store');

        Route::delete('/library/{resource}', [AcademicResourceController::class, 'destroy'])
            ->name('library.destroy');

        Route::get('/library/{resource}/edit', [AcademicResourceController::class, 'edit'])
            ->name('library.edit');

        Route::patch('/library/{resource}', [AcademicResourceController::class, 'update'])
            ->name('library.update');
    });

// Validation des inscriptions/paiements : ouverte à l'admin ET au superviseur.
Route::middleware([
    'auth',
    'verified',
    'role:admin,superviseur',
    'module.active:centre',
])
    ->prefix('admin/centre')
    ->name('admin.centre.')
    ->group(function () {
        Route::get('/pack-enrollments', [PackEnrollmentController::class, 'index'])
            ->name('pack-enrollments.index');

        Route::patch(
            '/pack-enrollments/{packEnrollment}/status',
            [AdminPackController::class, 'updateEnrollmentStatus']
        )->name('pack-enrollments.status');

        Route::post(
            '/pack-enrollments/{packEnrollment}/payments',
            [PackEnrollmentController::class, 'storePayment']
        )->name('pack-enrollments.payments.store');

        Route::post(
            '/pack-enrollments/{packEnrollment}/reminder',
            [PackEnrollmentController::class, 'sendReminder']
        )->name('pack-enrollments.reminder');

        Route::patch(
            '/pack-enrollments/{packEnrollment}/toggle-pause',
            [PackEnrollmentController::class, 'togglePause']
        )->name('pack-enrollments.toggle-pause');

        Route::get(
            '/pack-enrollments/{packEnrollment}/payments/{payment}/receipt',
            [PackEnrollmentController::class, 'receipt']
        )->name('pack-enrollments.payments.receipt');

        Route::get('/reports', [PaymentReportController::class, 'index'])
            ->name('reports.index');
    });

Route::middleware([
    'auth',
    'verified',
    'module.active:centre',
])
    ->prefix('student/courses')
    ->name('student.courses.')
    ->group(function () {
        Route::get('/', [CourseController::class, 'index'])
            ->name('index');

        Route::get('/{course}', [CourseController::class, 'show'])
            ->name('show');
    });

Route::middleware([
    'auth',
    'verified',
    'module.active:centre',
])
    ->prefix('student/packs')
    ->name('student.packs.')
    ->group(function () {
        Route::get('/', [StudentPackController::class, 'index'])
            ->name('index');

        Route::post('/{pack}/enroll', [StudentPackController::class, 'enroll'])
            ->name('enroll');
    });

Route::middleware([
    'auth',
    'verified',
    'module.active:centre',
])
    ->prefix('student/library')
    ->name('student.library.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Student\LibraryController::class, 'index'])
            ->name('index');
    });