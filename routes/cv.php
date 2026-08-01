<?php

use App\Http\Controllers\PublicPortfolioController;
use App\Http\Controllers\Student\CvController;
use Illuminate\Support\Facades\Route;

// --- Espace étudiant : CV builder ---
Route::middleware(['auth', 'verified'])
    ->prefix('cv')
    ->name('student.cv.')
    ->group(function () {
        Route::get('/', [CvController::class, 'edit'])->name('edit');
        Route::patch('/profile', [CvController::class, 'updateProfile'])->name('profile.update');
        Route::patch('/public', [CvController::class, 'togglePublic'])->name('public.toggle');

        Route::post('/educations', [CvController::class, 'storeEducation'])->name('educations.store');
        Route::patch('/educations/{education}', [CvController::class, 'updateEducation'])->name('educations.update');
        Route::delete('/educations/{education}', [CvController::class, 'destroyEducation'])->name('educations.destroy');

        Route::post('/experiences', [CvController::class, 'storeExperience'])->name('experiences.store');
        Route::patch('/experiences/{experience}', [CvController::class, 'updateExperience'])->name('experiences.update');
        Route::delete('/experiences/{experience}', [CvController::class, 'destroyExperience'])->name('experiences.destroy');

        Route::post('/skills', [CvController::class, 'storeSkill'])->name('skills.store');
        Route::delete('/skills/{skill}', [CvController::class, 'destroySkill'])->name('skills.destroy');

        Route::post('/languages', [CvController::class, 'storeLanguage'])->name('languages.store');
        Route::delete('/languages/{language}', [CvController::class, 'destroyLanguage'])->name('languages.destroy');

        Route::post('/certifications', [CvController::class, 'storeCertification'])->name('certifications.store');
        Route::delete('/certifications/{certification}', [CvController::class, 'destroyCertification'])->name('certifications.destroy');

        Route::post('/projects', [CvController::class, 'storeProject'])->name('projects.store');
        Route::patch('/projects/{project}', [CvController::class, 'updateProject'])->name('projects.update');
        Route::delete('/projects/{project}', [CvController::class, 'destroyProject'])->name('projects.destroy');

        Route::get('/download/cv', [CvController::class, 'showCv'])->name('download.cv');
        Route::get('/download/ats', [CvController::class, 'showAts'])->name('download.ats');
    });

// --- Espace admin : édition complète du CV d'un étudiant à sa place ---
Route::middleware(['auth', 'verified', 'role:admin,superviseur'])
    ->prefix('admin/cv')
    ->name('admin.cv.builder.')
    ->group(function () {
        Route::get('/{user}/edit', [\App\Http\Controllers\Admin\CvBuilderController::class, 'edit'])->name('edit');
        Route::patch('/{user}/profile', [\App\Http\Controllers\Admin\CvBuilderController::class, 'updateProfile'])->name('profile.update');
        Route::patch('/{user}/public', [\App\Http\Controllers\Admin\CvBuilderController::class, 'togglePublic'])->name('public.toggle');

        Route::post('/{user}/educations', [\App\Http\Controllers\Admin\CvBuilderController::class, 'storeEducation'])->name('educations.store');
        Route::patch('/educations/{education}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'updateEducation'])->name('educations.update');
        Route::delete('/educations/{education}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'destroyEducation'])->name('educations.destroy');

        Route::post('/{user}/experiences', [\App\Http\Controllers\Admin\CvBuilderController::class, 'storeExperience'])->name('experiences.store');
        Route::patch('/experiences/{experience}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'updateExperience'])->name('experiences.update');
        Route::delete('/experiences/{experience}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'destroyExperience'])->name('experiences.destroy');

        Route::post('/{user}/skills', [\App\Http\Controllers\Admin\CvBuilderController::class, 'storeSkill'])->name('skills.store');
        Route::delete('/skills/{skill}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'destroySkill'])->name('skills.destroy');

        Route::post('/{user}/languages', [\App\Http\Controllers\Admin\CvBuilderController::class, 'storeLanguage'])->name('languages.store');
        Route::delete('/languages/{language}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'destroyLanguage'])->name('languages.destroy');

        Route::post('/{user}/certifications', [\App\Http\Controllers\Admin\CvBuilderController::class, 'storeCertification'])->name('certifications.store');
        Route::delete('/certifications/{certification}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'destroyCertification'])->name('certifications.destroy');

        Route::post('/{user}/projects', [\App\Http\Controllers\Admin\CvBuilderController::class, 'storeProject'])->name('projects.store');
        Route::patch('/projects/{project}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'updateProject'])->name('projects.update');
        Route::delete('/projects/{project}', [\App\Http\Controllers\Admin\CvBuilderController::class, 'destroyProject'])->name('projects.destroy');
    });

// --- Portfolio public (sans authentification) ---
Route::get('/portfolio/{slug}', [PublicPortfolioController::class, 'show'])->name('portfolio.show');

// --- Espace admin : consultation des CV des étudiants ---
Route::middleware(['auth', 'verified', 'role:admin,superviseur'])
    ->prefix('admin/cv')
    ->name('admin.cv.')
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CvController::class, 'index'])->name('index');
        Route::get('/{user}', [\App\Http\Controllers\Admin\CvController::class, 'show'])->name('show');
        Route::get('/{user}/cv', [\App\Http\Controllers\Admin\CvController::class, 'showCv'])->name('download.cv');
        Route::get('/{user}/ats', [\App\Http\Controllers\Admin\CvController::class, 'showAts'])->name('download.ats');

        Route::get('/catalogue/skills', [\App\Http\Controllers\Admin\SkillSuggestionController::class, 'index'])->name('skills.index');
        Route::post('/catalogue/skills', [\App\Http\Controllers\Admin\SkillSuggestionController::class, 'store'])->name('skills.store');
        Route::patch('/catalogue/skills/{skill}', [\App\Http\Controllers\Admin\SkillSuggestionController::class, 'update'])->name('skills.update');
        Route::delete('/catalogue/skills/{skill}', [\App\Http\Controllers\Admin\SkillSuggestionController::class, 'destroy'])->name('skills.destroy');
    });
