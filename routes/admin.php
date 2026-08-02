<?php

use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware([
    'auth',
    'verified',
    'role:admin',
])
    ->prefix('admin/users')
    ->name('admin.users.')
    ->group(function () {
        Route::get('/', [UserController::class, 'index'])
            ->name('index');

        Route::post('/', [UserController::class, 'store'])
            ->name('store');

        Route::patch('/{user}/toggle-active', [UserController::class, 'toggleActive'])
            ->name('toggle-active');

        Route::delete('/{user}/roles/{role}', [UserController::class, 'destroyRole'])
            ->name('roles.destroy');
    });
