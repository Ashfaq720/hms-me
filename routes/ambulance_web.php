<?php


use App\Modules\Ambulance\Controllers\AmbulanceController;
use App\Modules\Ambulance\Controllers\AmbulanceRequestController;
use App\Modules\Ambulance\Controllers\AmbulanceTripController;
use App\Modules\Ambulance\Controllers\DriverController;
use App\Modules\Ambulance\Controllers\ErDashboardController;
use App\Modules\Ambulance\Controllers\ParamedicController;
use Illuminate\Support\Facades\Route;

Route::prefix('ambulance')->middleware(['auth'])->group(function () {

  Route::get('/', fn()=>redirect()->route('amb.requests.index'))->name('amb.home');

  // Masters
  Route::resource('ambulances', AmbulanceController::class)->names('amb.ambulances');

  Route::resource('drivers', DriverController::class)->names('amb.drivers');

  Route::resource('paramedics', controller: ParamedicController::class)->names('amb.paramedics');

  // Requests
  Route::resource('requests', AmbulanceRequestController::class)->names('amb.requests');

  // Dispatch/Trip
  Route::get('requests/{request}/assign', [AmbulanceTripController::class,'assignForm'])
      ->name('amb.trips.assignForm');

  Route::post('requests/{request}/assign', [AmbulanceTripController::class,'assignStore'])
      ->name('amb.trips.assignStore');

  Route::post('trips/{trip}/status', [AmbulanceTripController::class,'updateStatus'])
      ->name('amb.trips.updateStatus')->middleware('permission:ambulance.dispatch');

  // ER Dashboard
  Route::get('er/incoming', [ErDashboardController::class,'incoming'])
      ->name('amb.er.incoming');

});
