<?php

use App\Http\Controllers\Admin\RegistrationController as AdminRegistrationController;
use App\Http\Controllers\Student\RegistrationController as StudentRegistrationController;
use App\Http\Controllers\Student\RegistrationDocumentController;
use App\Http\Middleware\EnsureModuleIsActive;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    EnsureModuleIsActive::class . ':inscription',
])->group(function (): void {
    Route::prefix('student/registrations')
        ->name('student.registrations.')
        ->group(function (): void {
            Route::get(
                '/',
                [StudentRegistrationController::class, 'index']
            )->name('index');

            Route::get(
                '/create',
                [StudentRegistrationController::class, 'create']
            )->name('create');

            Route::post(
                '/',
                [StudentRegistrationController::class, 'store']
            )->name('store');

            Route::get(
                '/{registration}',
                [StudentRegistrationController::class, 'show']
            )->name('show');

            Route::get(
                '/{registration}/edit',
                [StudentRegistrationController::class, 'edit']
            )->name('edit');

            Route::patch(
                '/{registration}',
                [StudentRegistrationController::class, 'update']
            )->name('update');

            Route::patch(
                '/{registration}/submit',
                [StudentRegistrationController::class, 'submit']
            )->name('submit');

            Route::post(
                '/{registration}/documents',
                [RegistrationDocumentController::class, 'store']
            )->name('documents.store');

            Route::delete(
                '/documents/{document}',
                [RegistrationDocumentController::class, 'destroy']
            )->name('documents.destroy');
        });

    Route::get(
        '/registration-documents/{document}/download',
        [RegistrationDocumentController::class, 'download']
    )->name('registration-documents.download');

    Route::middleware([
        EnsureUserHasRole::class . ':admin',
    ])
        ->prefix('admin/registrations')
        ->name('admin.registrations.')
        ->group(function (): void {
            Route::get(
                '/',
                [AdminRegistrationController::class, 'index']
            )->name('index');

            Route::get(
                '/{registration}',
                [AdminRegistrationController::class, 'show']
            )->name('show');

            Route::patch(
                '/{registration}/status',
                [AdminRegistrationController::class, 'updateStatus']
            )->name('status.update');

            Route::patch(
                '/documents/{document}/verify',
                [AdminRegistrationController::class, 'verifyDocument']
            )->name('documents.verify');
        });
});