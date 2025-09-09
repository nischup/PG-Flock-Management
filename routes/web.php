<?php

use Inertia\Inertia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Master\FeedController;
use App\Http\Controllers\Master\ShedController;
use App\Http\Controllers\Master\UnitController;
use App\Http\Controllers\Master\FlockController;
use App\Http\Controllers\Ps\PsLabTestController;
use App\Http\Controllers\Ps\PsReceiveController;
use App\Http\Controllers\Master\CompanyController;

use App\Http\Controllers\Master\DiseaseController;
use App\Http\Controllers\Master\ProjectController;
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
use App\Http\Controllers\VaccineSchedule\VaccineRoutingController;
use App\Http\Controllers\Production\ProductionFirmReceiveController;
use App\Http\Controllers\Production\ProductionShedReceiveController;
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';



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
Route::resource('project', ProjectController::class);

Route::post('/labtest/getdata', [PsLabTestController::class, 'getData'])->name('labtest.getdata');
Route::resource('ps-lab-test', PsLabTestController::class);
Route::resource('shed-receive', ShedReceiveController::class);
Route::resource('batch-assign', BatchAssignController::class);
Route::resource('production-firm-receive', ProductionFirmReceiveController::class);
Route::resource('production-shed-receive', ProductionShedReceiveController::class);
Route::resource('vaccine-schedule', VaccineScheduleController::class);
Route::resource('bird-transfer', BirdTransferController::class);
Route::resource('vaccine-routing', VaccineRoutingController::class);

Route::prefix('daily-operation')->group(function () {
    Route::get('/stage/{stage}', [DailyOperationController::class, 'index'])
        ->where('stage', 'brooding|growing|laying|closing')
        ->name('daily-operation.stage');

    Route::get('/stage/{stage}/create', [DailyOperationController::class, 'create'])
        ->where('stage', 'brooding|growing|laying|closing')
        ->name('daily-operation.stage.create');

    Route::post('/store', [DailyOperationController::class, 'store'])
        ->name('daily-operation.store');
});


Route::get('/mortality/create', [DailyOperationController::class, 'mortality']);
Route::get('/overview', [DailyOperationController::class, 'overview']);
Route::get('/details/{flockId}/{tabKey}', [DailyOperationController::class, 'show']);

Route::resource('production/egg-classification', EggClassificationController::class);

Route::get('/weather', [WeatherController::class, 'get']);

Route::resource('flocks', FlockController::class)->only(['store']);


//Report
// Route::get('reports/vaccines/pdf', [VaccineController::class, 'downloadPdf'])->name('reports.vaccines.pdf');
// Route::get('reports/vaccines/excel', [VaccineController::class, 'downloadExcel'])->name('reports.vaccines.excel');
// Route::get('reports/units/pdf', [UnitController::class, 'downloadPdf'])->name('reports.unit.pdf');
// Route::get('reports/units/excel', [UnitController::class, 'downloadExcel'])->name('reports.unit.excel');


Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('disease/pdf', [DiseaseController::class, 'exportPdf'])->name('disease.pdf');
    Route::get('disease/excel', [DiseaseController::class, 'exportExcel'])->name('disease.excel');
    Route::get('vaccines/pdf', [VaccineController::class, 'exportPdf'])->name('vaccines.pdf');
    Route::get('vaccines/excel', [VaccineController::class, 'exportExcel'])->name('vaccines.excel');
    Route::get('units/pdf', [UnitController::class, 'downloadPdf'])->name('unit.pdf');
    Route::get('units/excel', [UnitController::class, 'downloadExcel'])->name('unit.excel');
    Route::get('company/pdf', [CompanyController::class, 'downloadPdf'])->name('company.pdf');
    Route::get('company/excel', [CompanyController::class, 'downloadExcel'])->name('company.excel');
    Route::get('project/pdf', [ProjectController::class, 'exportPdf'])->name('project.pdf');
    Route::get('project/excel', [ProjectController::class, 'exportExcel'])->name('project.excel');
    Route::get('supplier/pdf', [SupplierController::class, 'exportPdf'])->name('supplier.pdf');
    Route::get('supplier/excel', [SupplierController::class, 'exportExcel'])->name('supplier.excel');
    Route::get('ps-receive/pdf', [PsReceiveController::class, 'downloadPdf'])->name('ps-receive.pdf');
    Route::get('ps-receive/excel', [PsReceiveController::class, 'downloadExcel'])->name('ps-receive.excel');
    Route::get('medicine/pdf', [MedicineController::class, 'downloadPdf'])->name('medicine.pdf');
    Route::get('medicine/excel', [MedicineController::class, 'downloadExcel'])->name('medicine.excel');
    Route::get('feed/pdf', [FeedController::class, 'exportPdf'])->name('feed.pdf');
    Route::get('feed/excel', [FeedController::class, 'exportExcel'])->name('feed.excel');
    Route::get('shed/pdf', [ShedController::class, 'exportPdf'])->name('shed.pdf');
    Route::get('shed/excel', [ShedController::class, 'exportExcel'])->name('shed.excel');
    Route::get('vaccine-type/pdf', [VaccineTypeController::class, 'exportPdf'])->name('vaccine-type.pdf');
    Route::get('vaccine-type/excel', [VaccineTypeController::class, 'exportExcel'])->name('vaccine-type.excel');
    Route::get('feed-type/pdf', [FeedTypeController::class, 'exportPdf'])->name('feed-type.pdf');
    Route::get('feed-type/excel', [FeedTypeController::class, 'exportExcel'])->name('feed-type.excel');
    Route::get('chick-type/pdf', [ChickTypeController::class, 'exportPdf'])->name('chick-type.pdf');
    Route::get('chick-type/excel', [ChickTypeController::class, 'exportExcel'])->name('chick-type.excel');
    Route::get('breed-type/pdf', [BreedTypeController::class, 'exportPdf'])->name('breed-type.pdf');
    Route::get('breed-type/excel', [BreedTypeController::class, 'exportExcel'])->name('breed-type.excel');
});




Route::get('/ps-receive/{id}/pdf', [PsReceiveController::class, 'downloadRowPdf'])
    ->name('ps-receive.row-pdf');

Route::get('/bird-transfer/create/{batchAssignid}', [BirdTransferController::class, 'create']);
