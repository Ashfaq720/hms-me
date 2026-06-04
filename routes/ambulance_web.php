<?php

use App\Modules\Ambulance\Controllers\AmbulanceController;
use App\Modules\Ambulance\Controllers\AmbulanceRequestController;
use App\Modules\Ambulance\Controllers\AmbulanceTripController;
use App\Modules\Ambulance\Controllers\ClinicalNoteController;
use App\Modules\Ambulance\Controllers\DriverController;
use App\Modules\Ambulance\Controllers\ErDashboardController;
use App\Modules\Ambulance\Controllers\ParamedicController;
use App\Modules\Ambulance\Controllers\VendorController;
use Illuminate\Support\Facades\Route;

Route::prefix('ambulance')->middleware(['auth'])->group(function () {

    Route::get('/', fn() => redirect()->route('amb.requests.index'))->name('amb.home');

    // --- Masters ---
    Route::resource('ambulances', AmbulanceController::class)->names('amb.ambulances');
    Route::post('ambulances/{ambulance}/equipment', [AmbulanceController::class, 'equipmentStore'])
        ->name('amb.ambulances.equipment.store');
    Route::delete('ambulances/{ambulance}/equipment/{equipment}', [AmbulanceController::class, 'equipmentDestroy'])
        ->name('amb.ambulances.equipment.destroy');
    Route::resource('drivers', DriverController::class)->names('amb.drivers');
    Route::resource('paramedics', ParamedicController::class)->names('amb.paramedics');

    // --- Vendors ---
    Route::resource('vendors', VendorController::class)->names('amb.vendors');
    Route::get('vendors/{vendor}/contracts/create', [VendorController::class, 'contractCreate'])
        ->name('amb.vendors.contract.create');
    Route::post('vendors/{vendor}/contracts', [VendorController::class, 'contractStore'])
        ->name('amb.vendors.contract.store');

    // --- Requests ---
    Route::resource('requests', AmbulanceRequestController::class)->names('amb.requests');

    // --- Dispatch / Trip ---
    Route::get('requests/{request}/assign', [AmbulanceTripController::class, 'assignForm'])
        ->name('amb.trips.assignForm');
    Route::post('requests/{request}/assign', [AmbulanceTripController::class, 'assignStore'])
        ->name('amb.trips.assignStore');
    Route::get('trips/{trip}', [AmbulanceTripController::class, 'show'])
        ->name('amb.trips.show');
    Route::post('trips/{trip}/status', [AmbulanceTripController::class, 'updateStatus'])
        ->name('amb.trips.updateStatus')
        ->middleware('permission:ambulance.dispatch');
    Route::get('trips/{trip}/edit', [AmbulanceTripController::class, 'edit'])
        ->name('amb.trips.edit');
    Route::put('trips/{trip}', [AmbulanceTripController::class, 'update'])
        ->name('amb.trips.update');
    Route::get('trips/{trip}/reassign', [AmbulanceTripController::class, 'reassignForm'])
        ->name('amb.trips.reassign');
    Route::post('trips/{trip}/reassign', [AmbulanceTripController::class, 'reassignStore'])
        ->name('amb.trips.reassignStore');

    // --- Clinical Notes ---
    Route::get('trips/{trip}/clinical-notes/create', [ClinicalNoteController::class, 'create'])
        ->name('amb.trips.clinical_notes.create');
    Route::post('trips/{trip}/clinical-notes', [ClinicalNoteController::class, 'store'])
        ->name('amb.trips.clinical_notes.store');

    // --- ER Dashboard ---
    Route::get('er/incoming', [ErDashboardController::class, 'incoming'])
        ->name('amb.er.incoming');
});
