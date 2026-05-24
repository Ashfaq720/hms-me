<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Pathology\PathologyController;

Route::middleware('auth')->group(function () {
    Route::get('pathology', [PathologyController::class, 'index'])->name('pathology.index');
    Route::post('pathology', [PathologyController::class, 'store'])->name('pathology.store');
    Route::get('pathology/{id}', [PathologyController::class, 'show'])->name('pathology.show');

    Route::post('pathology/{order}/requests/{request}/file', [PathologyController::class, 'uploadFile'])
        ->name('pathology.requests.file.upload');
    Route::delete('pathology/{order}/requests/{request}/file', [PathologyController::class, 'deleteFile'])
        ->name('pathology.requests.file.delete');
    Route::patch('pathology/{order}/requests/{request}/status', [PathologyController::class, 'updateStatus'])
        ->name('pathology.requests.status.update');
});
