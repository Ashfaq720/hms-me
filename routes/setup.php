<?php

use App\Http\Controllers\Setup\IpdPatientPackageController;
use App\Http\Controllers\Setup\PackageManagementController;
use App\Http\Controllers\Setup\ServicePackageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Setup / Master Data Routes
|--------------------------------------------------------------------------
|
| Currently scoped to Service Packages master. Other Setup masters (which
| live in MasterData/OT Setup controllers) keep their own route files —
| this one is dedicated to cross-module masters like Service Packages.
|
*/

Route::middleware(['auth'])
    ->prefix('setup')
    ->name('setup.')
    ->group(function () {

        Route::resource('service-packages', ServicePackageController::class);

        // IPD patient package status transitions
        Route::prefix('ipd-patient-packages')->name('ipd-patient-packages.')->group(function () {
            Route::post('{ipdPatientPackage}/approve',  [IpdPatientPackageController::class, 'approve'])->name('approve');
            Route::post('{ipdPatientPackage}/cancel',   [IpdPatientPackageController::class, 'cancel'])->name('cancel');
            Route::post('{ipdPatientPackage}/complete', [IpdPatientPackageController::class, 'complete'])->name('complete');
            Route::post('{ipdPatientPackage}/close',    [IpdPatientPackageController::class, 'close'])->name('close');
        });

    });

// Cross-admission Package Assignments list — lives at /package-assignments
// (top-level so it can be reached from the Package Management menu).
Route::middleware(['auth'])
    ->prefix('package-assignments')
    ->name('package-assignments.')
    ->group(function () {
        Route::get('/', [IpdPatientPackageController::class, 'index'])->name('index');
    });

// Package Management dashboard + drill-down landings.
Route::middleware(['auth'])
    ->prefix('package-management')
    ->name('package-management.')
    ->group(function () {
        Route::get('/',              [PackageManagementController::class, 'dashboard'])      ->name('dashboard');
        Route::get('/categories',    [PackageManagementController::class, 'categories'])     ->name('categories');
        Route::get('/pending',       [PackageManagementController::class, 'pendingApproval'])->name('pending');
    });
