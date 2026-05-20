<?php

use App\Http\Controllers\Billing\BillingController;
use App\Http\Controllers\Billing\IpdBillingController;
use App\Http\Controllers\Billing\OPDBillingController;
use Illuminate\Support\Facades\Route;

Route::prefix('billing')
    ->middleware(['auth'])
    ->name('billing.')
    ->group(function () {

        Route::resource('/', BillingController::class)->middleware('auth');
        Route::resource('/ipd-billing', IpdBillingController::class)->middleware('auth');
        Route::resource('/opd-billing', OPDBillingController::class)->middleware('auth');
    });
