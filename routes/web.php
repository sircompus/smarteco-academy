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