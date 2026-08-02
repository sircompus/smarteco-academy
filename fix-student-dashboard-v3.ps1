$path0 = "C:\laragon\www\SEA\resources\views\student\dashboard.blade.php"
$content0 = @'
@extends('layouts.student')

@section('title', 'Tableau de bord étudiant')

@section('page-title', 'Tableau de bord étudiant')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-900">
            Bonjour {{ auth()->user()->name }}
        </h2>

        <p class="mt-1 text-gray-600">
            Retrouvez vos formations, inscriptions et services.
        </p>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Modules disponibles
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $activeModules->count() }}
            </p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Inscriptions (packs)
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $packEnrollmentsCount }}
            </p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Formations suivies
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ $trainingEnrollmentsCount }}
            </p>
        </div>

        <div class="rounded-2xl bg-white p-6 shadow-sm">
            <p class="text-sm font-medium text-gray-500">
                Notifications
            </p>

            <p class="mt-3 text-3xl font-bold text-gray-900">
                {{ auth()->user()->unreadNotifications()->count() }}
            </p>
        </div>
    </div>

    <section class="mt-8">
        <h3 class="text-lg font-bold text-gray-900">
            Modules actifs
        </h3>

        <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @forelse ($activeModules as $module)
                @php
                    $routeName = $moduleRoutes[$module->slug] ?? null;
                    $hasRoute = $routeName && \Illuminate\Support\Facades\Route::has($routeName);
                @endphp

                @if ($hasRoute)
                    <a href="{{ route($routeName) }}" class="block rounded-2xl bg-white p-5 shadow-sm transition hover:shadow-md">
                        <h4 class="font-semibold text-indigo-700">
                            {{ $module->name }}
                        </h4>

                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            {{ $module->description }}
                        </p>

                        <p class="mt-3 text-xs font-semibold text-indigo-600">Accéder →</p>
                    </a>
                @else
                    <article class="rounded-2xl bg-gray-50 p-5 opacity-75">
                        <h4 class="font-semibold text-gray-900">
                            {{ $module->name }}
                        </h4>

                        <p class="mt-2 text-sm leading-6 text-gray-600">
                            {{ $module->description }}
                        </p>

                        <p class="mt-3 text-xs font-semibold text-gray-400">Bientôt disponible</p>
                    </article>
                @endif
            @empty
                <p class="text-gray-500">
                    Aucun module actif.
                </p>
            @endforelse
        </div>
    </section>
@endsection
'@
$dir0 = Split-Path $path0 -Parent
if (-not (Test-Path $dir0)) { New-Item -ItemType Directory -Path $dir0 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path0, $content0, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: resources/views/student/dashboard.blade.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: resources/views/student/dashboard.blade.php -- $($_.Exception.Message)" -ForegroundColor Red
}

$path1 = "C:\laragon\www\SEA\routes\web.php"
$content1 = @'
<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.home')
    ->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function (Request $request) {
        if ($request->user()->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        return redirect()->route('student.dashboard');
    })->name('dashboard');

    Route::get('/student/dashboard', function () {
        $activeModules = DB::table('modules')
            ->where('is_active', true)
            ->orderBy('menu_order')
            ->get();

        $user = auth()->user();

        return view('student.dashboard', [
            'activeModules' => $activeModules,
            'packEnrollmentsCount' => $user->packEnrollments()->count(),
            'trainingEnrollmentsCount' => $user->trainingEnrollments()->count(),
            'moduleRoutes' => [
                'centre' => 'student.packs.index',
                'inscription' => 'student.registrations.index',
                'formations' => 'student.trainings.index',
                'cv-ats' => 'student.cv.edit',
                'portfolio' => 'student.cv.edit',
            ],
        ]);
    })->name('student.dashboard');

    Route::get('/admin/dashboard', function (Request $request) {
        abort_unless(
            $request->user()->hasRole('admin'),
            403,
            'Accès réservé aux administrateurs.'
        );

        return view('admin.dashboard', [
            'usersCount' => DB::table('users')->count(),
            'activeModulesCount' => DB::table('modules')
                ->where('is_active', true)
                ->count(),
            'rolesCount' => DB::table('roles')->count(),
            'activityLogsCount' => DB::table('activity_logs')->count(),
        ]);
    })->name('admin.dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])
        ->name('profile.avatar.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
require __DIR__.'/centre.php';
require __DIR__.'/registration.php';
require __DIR__.'/training.php';
require __DIR__.'/cv.php';
'@
$dir1 = Split-Path $path1 -Parent
if (-not (Test-Path $dir1)) { New-Item -ItemType Directory -Path $dir1 -Force | Out-Null }
try {
    [System.IO.File]::WriteAllText($path1, $content1, [System.Text.UTF8Encoding]::new($false))
    Write-Host "OK: routes/web.php" -ForegroundColor Green
} catch {
    Write-Host "ECHEC: routes/web.php -- $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "=== VERIFICATION ===" -ForegroundColor Cyan
Select-String -Path "C:\laragon\www\SEA\resources\views\student\dashboard.blade.php" -Pattern "packEnrollmentsCount"
if ($?) { Write-Host "Le fichier contient bien le correctif." -ForegroundColor Green } else { Write-Host "ATTENTION: le correctif ne semble PAS present." -ForegroundColor Red }
