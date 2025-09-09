<?php

use App\Http\Controllers\Ps\PsFirmReceiveController;
use App\Http\Controllers\Ps\PsReceiveController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\UserRegisterController;
use App\Http\Controllers\UserRoleController;
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

    Route::resource('ps-receive', PsReceiveController::class);
    Route::resource('ps-firm-receive', PsFirmReceiveController::class)->except(['show']);
    Route::get('/ps-receive/{id}/data', [PsReceiveController::class, 'getData']);
    Route::post('ps-receive/storelab', [PsReceiveController::class, 'storelab']);
    Route::get('/ps-receive/suppliers-by-shipment-type', [PsReceiveController::class, 'getSuppliersByShipmentType']);
    Route::resource('user-role', UserRoleController::class)->except(['show']);

    Route::get('/user-register', [UserRegisterController::class, 'index'])->middleware('permission:user.view')->name('users.index');
    Route::get('/user-register/create', [UserRegisterController::class, 'create'])->middleware('permission:user.create')->name('users.create');
    Route::post('/user-register', [UserRegisterController::class, 'store'])->middleware('permission:user.create')->name('users.store');
    Route::get('/user-register/{user}/edit', [UserRegisterController::class, 'edit'])->middleware('permission:user.edit')->name('users.edit');
    Route::put('/user-register/{user}', [UserRegisterController::class, 'update'])->middleware('permission:user.edit')->name('users.update');
    Route::delete('/user-register/{user}', [UserRegisterController::class, 'destroy'])->middleware('permission:user.delete')->name('users.destroy');

});
