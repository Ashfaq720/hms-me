<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Radiology\RadiologyController;

Route::middleware('auth')->group(function () {
    Route::get('radiology', [RadiologyController::class, 'index'])->name('radiology.index');
    Route::post('radiology', [RadiologyController::class, 'store'])->name('radiology.store');
    Route::get('radiology/{id}', [RadiologyController::class, 'show'])->name('radiology.show');

    Route::post('radiology/{order}/requests/{request}/file', [RadiologyController::class, 'uploadFile'])
        ->name('radiology.requests.file.upload');
    Route::delete('radiology/{order}/requests/{request}/file', [RadiologyController::class, 'deleteFile'])
        ->name('radiology.requests.file.delete');
    Route::patch('radiology/{order}/requests/{request}/status', [RadiologyController::class, 'updateStatus'])
        ->name('radiology.requests.status.update');
});
