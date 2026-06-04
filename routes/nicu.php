<?php

use App\Http\Controllers\Nicu\NicuDashboardController;
use Illuminate\Support\Facades\Route;

Route::prefix('nicu')
    ->middleware(['auth'])
    ->name('nicu.')
    ->group(function () {
        Route::get('/', [NicuDashboardController::class, 'dashboard'])->name('dashboard');
        Route::get('/admissions', [NicuDashboardController::class, 'admissionsIndex'])->name('admissions.index');
        Route::get('/admissions/{id}', [NicuDashboardController::class, 'admissionShow'])->name('admissions.show');
        Route::get('/resources', [NicuDashboardController::class, 'resourcesIndex'])->name('resources.index');
        Route::get('/vitals', [NicuDashboardController::class, 'vitalsIndex'])->name('vitals.index');
        Route::get('/feeding', [NicuDashboardController::class, 'feedingIndex'])->name('feeding.index');
        Route::get('/growth', [NicuDashboardController::class, 'growthIndex'])->name('growth.index');
        Route::get('/medications', [NicuDashboardController::class, 'medicationsIndex'])->name('medications.index');
        Route::get('/procedures', [NicuDashboardController::class, 'proceduresIndex'])->name('procedures.index');
        Route::get('/infections', [NicuDashboardController::class, 'infectionsIndex'])->name('infections.index');
        Route::get('/consents', [NicuDashboardController::class, 'consentsIndex'])->name('consents.index');
    });
