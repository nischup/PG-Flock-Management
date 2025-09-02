<?php

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\Master\FeedController;
use App\Http\Controllers\Master\ShedController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Ps\PsLabTestController;
use App\Http\Controllers\Master\CompanyController;
use App\Http\Controllers\Master\DiseaseController;
use App\Http\Controllers\Master\VaccineController;
use App\Http\Controllers\Master\FeedTypeController;
use App\Http\Controllers\Master\MedicineController;
use App\Http\Controllers\Master\SupplierController;
use App\Http\Controllers\Master\BreedTypeController;
use App\Http\Controllers\Master\ChickTypeController;
use App\Http\Controllers\Shed\BatchAssignController;
use App\Http\Controllers\Shed\ShedReceiveController;
use App\Http\Controllers\Master\VaccineTypeController;
use App\Http\Controllers\Transfer\BirdTransferController;
use App\Http\Controllers\Production\EggClassificationController;
use App\Http\Controllers\DailyOperation\DailyOperationController;
use App\Http\Controllers\VaccineSchedule\VaccineScheduleController;
use App\Http\Controllers\Production\ProductionFirmReceiveController;
use App\Http\Controllers\DashboardController;
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

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
Route::resource('disease', DiseaseController::class);
Route::resource('supplier', SupplierController::class);
Route::resource('breed-type', BreedTypeController::class);

Route::post('/labtest/getdata', [PsLabTestController::class, 'getData'])->name('labtest.getdata');
Route::resource('ps-lab-test', PsLabTestController::class);
Route::resource('shed-receive', ShedReceiveController::class);
Route::resource('batch-assign', BatchAssignController::class);
Route::resource('production-firm-receive', ProductionFirmReceiveController::class);
Route::resource('vaccine-schedule', VaccineScheduleController::class);
Route::resource('bird-transfer', BirdTransferController::class);

Route::prefix('daily-operation')->group(function () {
    Route::get('/stage/{stage}', [DailyOperationController::class, 'index'])
        ->where('stage', 'brooding|growing|laying|closing')
        ->name('daily-operation.stage');

    Route::get('/stage/{stage}/create', [DailyOperationController::class, 'create'])
        ->where('stage', 'brooding|growing|laying|closing')
        ->name('daily-operation.stage.create');
});


Route::get('/mortality/create', [DailyOperationController::class, 'mortality']);
Route::get('/overview', [DailyOperationController::class, 'overview']);
Route::get('/details/{flockId}/{tabKey}', [DailyOperationController::class, 'show']);

Route::resource('production/egg-classification', EggClassificationController::class);

Route::get('/weather', [WeatherController::class, 'get']);


//Report
Route::get('reports/vaccines/pdf', [VaccineController::class, 'downloadPdf'])->name('reports.vaccines.pdf');
Route::get('reports/vaccines/excel', [VaccineController::class, 'downloadExcel'])->name('reports.vaccines.excel');
Route::get('reports/units/pdf', [UnitController::class, 'downloadPdf'])->name('reports.unit.pdf');
Route::get('reports/units/excel', [UnitController::class, 'downloadExcel'])->name('reports.unit.excel');


Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('disease/pdf', [DiseaseController::class, 'exportPdf'])->name('disease.pdf');
    Route::get('disease/excel', [DiseaseController::class, 'exportExcel'])->name('disease.excel');
    Route::get('vaccines/pdf', [VaccineController::class, 'downloadPdf'])->name('vaccines.pdf');
Route::get('vaccines/excel', [VaccineController::class, 'downloadExcel'])->name('vaccines.excel');
Route::get('units/pdf', [UnitController::class, 'downloadPdf'])->name('unit.pdf');
Route::get('units/excel', [UnitController::class, 'downloadExcel'])->name('unit.excel');
});
