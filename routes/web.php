<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Master\ChickTypeController;
use App\Http\Controllers\Master\FeedController;
use App\Http\Controllers\Master\FeedTypeController;
use App\Http\Controllers\Master\LocationController;
use App\Http\Controllers\Master\CompanyController;
use App\Http\Controllers\Master\MedicineController;
use App\Http\Controllers\Master\ShedController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Master\VaccineController;
use App\Http\Controllers\Master\VaccineTypeController;
use App\Http\Controllers\Ps\PsLabTestController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\Shed\ShedReceiveController;
use App\Http\Controllers\Shed\FlockAssignController;
use App\Http\Controllers\DailyOperation\DailyOperationController;
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';



Route::resource('chick-type', ChickTypeController::class);
Route::resource('feed', FeedController::class);
Route::resource('feed-type', FeedTypeController::class);
Route::resource('company', CompanyController::class);
Route::resource('medicine', MedicineController::class);
Route::resource('shed', ShedController::class);
Route::resource('unit', UnitController::class);
Route::resource('vaccine', VaccineController::class);
Route::resource('vaccine-type', VaccineTypeController::class);
Route::resource('supplier', SupplierController::class);

Route::post('/labtest/getdata', [PsLabTestController::class, 'getData'])->name('labtest.getdata');
Route::resource('ps-lab-test', PsLabTestController::class);
Route::resource('receive', ShedReceiveController::class);
Route::resource('flock-assign', FlockAssignController::class);
Route::resource('daily-operation', DailyOperationController::class);