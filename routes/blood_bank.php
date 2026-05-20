<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BloodBank\BloodBankController;
use App\Http\Controllers\BloodBank\BloodCollectionController;
use App\Http\Controllers\BloodBank\ComponentCollectionController;
use App\Http\Controllers\BloodBank\BloodIssueController;

Route::middleware('auth')->group(function () {
    Route::resource('blood-bank', BloodBankController::class);
    Route::resource('blood-collections', BloodCollectionController::class)->only(['store', 'update', 'destroy']);
    Route::resource('component-collections', ComponentCollectionController::class)->only(['store', 'destroy']);
    Route::get('blood-issues', [BloodIssueController::class, 'index'])->name('blood-issues.index');
    Route::post('blood-issues', [BloodIssueController::class, 'store'])->name('blood-issues.store');
});
