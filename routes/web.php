<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\UserRegisterController;
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\Master\ChickTypeController;
use App\Http\Controllers\Master\FeedController;
use App\Http\Controllers\Master\FeedTypeController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\MedicineController;
use App\Http\Controllers\Master\ShedController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Master\VaccineController;
use App\Http\Controllers\DOC\DOCReceiveController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';

Route::get('user-register', [UserRegisterController::class, 'index'])->name('user-register');
Route::get('doc-receive', [DOCReceiveController::class, 'index'])->name('doc-receive');
//Route::get('user-role', [UserRoleController::class, 'index'])->name('user-role');
Route::get('chick-type', [ChickTypeController::class, 'index'])->name('chick-type');
Route::get('feed', [FeedController::class, 'index'])->name('feed');
Route::get('feed-type', [FeedTypeController::class, 'index'])->name('feed-type');
Route::get('location', [LocationController::class, 'index'])->name('location');
Route::get('medicine', [MedicineController::class, 'index'])->name('medicine');
Route::get('shed', [ShedController::class, 'index'])->name('shed');
Route::get('unit', [UnitController::class, 'index'])->name('medicine');
Route::get('vaccine', [VaccineController::class, 'index'])->name('vaccine');
