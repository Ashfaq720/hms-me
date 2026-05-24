<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OPD\ConsultationNoteController;
use App\Http\Controllers\OPD\OpdChargeController;
use App\Http\Controllers\OPD\OpdLabOrderController;
use App\Http\Controllers\OPD\OpdMedicationController;
use App\Http\Controllers\OPD\OpdPaymentController;
use App\Http\Controllers\OPD\OpdPrescriptionController;
use App\Http\Controllers\OPD\OpdPatientController;
use App\Http\Controllers\OPD\PatientHistoryController;
use App\Http\Controllers\OPD\VitalCheckController;

Route::middleware('auth')->group(function () {

    Route::get('opd-patients/get-doctor-opd-fee', [OpdPatientController::class, 'getDoctorOpdFee'])->name('opd-patients.get-doctor-opd-fee');
    Route::get('opd-patients/patient-visit-history', [OpdPatientController::class, 'getPatientVisitHistory'])->name('opd-patients.patient-visit-history');

    // Unified Lab Order (all 9 lab investigation types · multi-line) — mirror of IPD
    Route::get('opd-patients/{id}/lab-orders/create', [OpdLabOrderController::class, 'create'])->name('opd-patients.lab-orders.create');
    Route::post('opd-patients/{id}/lab-orders',       [OpdLabOrderController::class, 'store'])->name('opd-patients.lab-orders.store');

    Route::resource('opd-patients', OpdPatientController::class);

    Route::prefix('opd-patients')
        ->name('opd-patients.')
        ->group(function () {

            Route::controller(OpdPatientController::class)->group(function () {
                Route::post('{opdPatient}/documents', 'storeDocument')->name('documents.store');
                Route::delete('{opdPatient}/documents/{document}', 'destroyDocument')->name('documents.destroy');
                Route::post('{opdPatient}/recheckup', 'recheckup')->name('recheckup');
                Route::get('{id}/print', 'print')->name('print');
                Route::get('{id}/move-to-ipd', 'moveToIpdForm')->name('move-to-ipd.form');
                Route::post('{id}/move-to-ipd', 'moveToIpd')->name('move-to-ipd');
                Route::get('{id}/bill', 'bill')->name('bill');
                Route::get('{id}/prescription/create', 'createPrescription')->name('prescription.create');

                Route::get('{id}/details-modal', 'detailsModal')->name('details.modal');
                Route::get('{id}/manual-prescription-modal', 'manualPrescriptionModal')->name('manual-prescription.modal');
                Route::post('{id}/manual-prescription-store', 'manualPrescriptionStore')->name('manual-prescription.store');
            });

            Route::controller(OpdMedicationController::class)->group(function () {
                Route::get('{id}/medications/create', 'create')->name('medications.create');
                Route::post('{id}/medications', 'store')->name('medications.store');
                Route::delete('{id}/medications/{medicationId}', 'destroy')->name('medications.destroy');
            });

            Route::controller(OpdChargeController::class)->group(function () {
                Route::get('{id}/charges/create', 'create')->name('charges.create');
                Route::post('{id}/charges', 'store')->name('charges.store');
                Route::get('{id}/charges/{chargeId}/edit', 'edit')->name('charges.edit');
                Route::put('{id}/charges/{chargeId}', 'update')->name('charges.update');
                Route::delete('{id}/charges/{chargeId}', 'destroy')->name('charges.destroy');
            });

            Route::controller(OpdPaymentController::class)->group(function () {
                Route::get('{id}/payments/create', 'create')->name('payments.create');
                Route::post('{id}/payments', 'store')->name('payments.store');
                Route::get('{id}/payments/{transactionId}/edit', 'edit')->name('payments.edit');
                Route::put('{id}/payments/{transactionId}', 'update')->name('payments.update');
                Route::delete('{id}/payments/{transactionId}', 'destroy')->name('payments.destroy');
            });

            Route::controller(OpdPrescriptionController::class)->group(function () {
                Route::get('{id}/prescriptions/create', 'create')->name('prescriptions.create');
                Route::post('{id}/prescriptions', 'store')->name('prescriptions.store');
                Route::get('{id}/prescriptions/{prescriptionId}', 'show')->name('prescriptions.show');
                Route::get('{id}/prescriptions/{prescriptionId}/pdf', 'pdf')->name('prescriptions.pdf');
                Route::delete('{id}/prescriptions/{prescriptionId}', 'destroy')->name('prescriptions.destroy');
            });

            Route::controller(VitalCheckController::class)->group(function () {
                Route::get('{id}/vital-checks', 'create')->name('vital-checks');
                Route::post('{id}/vital-checks', 'store')->name('vital-checks.store');
            });

            Route::controller(PatientHistoryController::class)->group(function () {
                Route::post('{opdPatient}/histories', 'store')->name('histories.store');
                Route::put('{opdPatient}/histories/{history}', 'update')->name('histories.update');
                Route::delete('{opdPatient}/histories/{history}', 'destroy')->name('histories.destroy');
            });

            Route::controller(ConsultationNoteController::class)->group(function () {
                Route::post('{opdPatient}/consultation-note', 'store')->name('consultation-note.store');
                Route::post('{opdPatient}/consultation-note/close', 'close')->name('consultation-note.close');
            });
        });
});
