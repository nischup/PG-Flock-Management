<?php

use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\UserRegisterController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');

    Route::put('settings/password', [PasswordController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('settings/Appearance');
    })->name('appearance');

    Route::resource('user-role', UserRoleController::class)->only([
    'index', 'create', 'store', 'edit', 'update', 'destroy'
]);




    Route::get('/users', [UserRegisterController::class, 'index'])->middleware('permission:user.view')->name('users.index');
    Route::get('/users/create', [UserRegisterController::class, 'create'])->middleware('permission:user.create')->name('users.create');
    Route::post('/users', [UserRegisterController::class, 'store'])->middleware('permission:user.create')->name('users.store');
    Route::get('/users/{user}/edit', [UserRegisterController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [UserRegisterController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [UserRegisterController::class, 'destroy'])->middleware('permission:user.delete')->name('users.destroy');



});
