<?php

use App\Http\Controllers\Er\ErModuleController;
use Illuminate\Support\Facades\Route;

Route::prefix('er')
    ->middleware(['auth'])
    ->name('er.')
    ->group(function () {
        Route::get('/', [ErModuleController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard', [ErModuleController::class, 'dashboard'])->name('dashboard.alias');
        Route::get('/board', [ErModuleController::class, 'board'])->name('board');
        Route::get('/{id}', [ErModuleController::class, 'show'])->name('show');
        Route::post('/{id}/triage', [ErModuleController::class, 'storeTriage'])->name('triage.store');
        Route::post('/{id}/note', [ErModuleController::class, 'storeNote'])->name('note.store');
        Route::post('/{id}/observation', [ErModuleController::class, 'storeObservation'])->name('observation.store');
        Route::post('/{id}/transfer', [ErModuleController::class, 'transfer'])->name('transfer');
    });
