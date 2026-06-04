<?php

use App\Http\Controllers\Er\ErDashboardController;
use App\Http\Controllers\Er\ErModuleController;
use App\Http\Controllers\Er\ErPatientController;
use Illuminate\Support\Facades\Route;

Route::prefix('er')
    ->middleware(['auth'])
    ->name('er.')
    ->group(function () {

        /* ───── New ER Module endpoints (id-based) ───── */
        Route::get('/',          [ErModuleController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard', [ErDashboardController::class, 'index'])->name('dashboard.alias');
        Route::get('/board',     [ErModuleController::class, 'board'])->name('board');

        // Patient lifecycle actions (numeric id required)
        Route::post('/{id}/triage',      [ErModuleController::class, 'storeTriage'])->whereNumber('id')->name('triage.store');
        Route::post('/{id}/note',        [ErModuleController::class, 'storeNote'])->whereNumber('id')->name('note.store');
        Route::post('/{id}/observation', [ErModuleController::class, 'storeObservation'])->whereNumber('id')->name('observation.store');
        Route::post('/{id}/transfer',    [ErModuleController::class, 'transfer'])->whereNumber('id')->name('transfer');

        /* ───── Legacy patient-centric endpoints (resources/views/er/patients/*) ───── */
        Route::get('/patients',                       [ErPatientController::class, 'index'])->name('patients.index');
        Route::get('/patients/{erPatient}',           [ErPatientController::class, 'show'])->name('patients.show');
        Route::put('/patients/{erPatient}',           [ErPatientController::class, 'update'])->name('patients.update');
        Route::post('/patients/{erPatient}/transfer', [ErPatientController::class, 'transferOut'])->name('patients.transfer');
        Route::post('/patients/{erPatient}/close',    [ErPatientController::class, 'close'])->name('patients.close');
        Route::get('test/route',    [ErPatientController::class, 'est'])->name('test');

        // Numeric-id show LAST — constrained so /patients, /dashboard, /board aren't
        // greedily captured by /{id}.
        Route::get('/{id}', [ErModuleController::class, 'show'])->whereNumber('id')->name('show');
    });
