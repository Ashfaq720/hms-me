<?php

use App\Http\Controllers\IPD\BedAllocationController;
use App\Http\Controllers\IPD\CaseDrController;
use App\Http\Controllers\IPD\CaseOperationController;
use App\Http\Controllers\IPD\IpdChargeController;
use App\Http\Controllers\IPD\IpdMedicationController;
use App\Http\Controllers\IPD\IpdPatientController;
use App\Http\Controllers\IPD\IpdPrescriptionController;
use App\Http\Controllers\IPD\MedicineOrderController;
use App\Http\Controllers\IPD\NurseNoteController;
use App\Http\Controllers\IPD\PathologyOrderController;
use App\Http\Controllers\IPD\PaymentController;
use App\Http\Controllers\IPD\RadiologyOrderController;
use App\Http\Controllers\IPD\RoundDrController;
use App\Http\Controllers\IPD\SurgeryRequestController;
use App\Http\Controllers\IPD\TreatmentHistoryController;
use App\Http\Controllers\IPD\VitalCheckController;
use Illuminate\Support\Facades\Route;

Route::prefix('ipd-patients')
    ->middleware(['auth'])
    ->name('ipd-patients.')
    ->group(function () {

        Route::resource('/', IpdPatientController::class)->middleware('auth');
        Route::get('/{id}/edit', [IpdPatientController::class, 'edit'])->name('ipd-patients.edit');
        Route::get('/{iPDPatient}', [IpdPatientController::class, 'show'])->name('ipd-patients.show');
        Route::put('/{id}', [IpdPatientController::class, 'update'])->name('ipd-patients.update');
        Route::get('/{id}/bed-transfer', [IpdPatientController::class, 'bedTransfer'])->name('bed-transfer');
        Route::post('/{id}/bed-transfer', [IpdPatientController::class, 'bedTransferStore'])->name('bed-transfer.store');
        Route::post('/{id}/convert-to-opd', [IpdPatientController::class, 'convertToOpd'])->name('convert-to-opd');

        // ICU transfer (bed -> ICU). Use the existing bed-transfer endpoint
        // for ICU -> regular bed; it correctly tags allocation_type from bed type.
        Route::get('/{id}/icu-transfer', [IpdPatientController::class, 'icuTransfer'])->name('icu-transfer');
        Route::post('/{id}/icu-transfer', [IpdPatientController::class, 'icuTransferStore'])->name('icu-transfer.store');

        Route::put('/{id}/discharge-request', [IpdPatientController::class, 'dischargeRequest'])->name('discharge-request');

        // Print slips
        Route::get('/{id}/admission-slip', [IpdPatientController::class, 'admissionSlip'])->name('ipd-patients.admission-slip');
        Route::get('/{id}/payment-slip/{transactionId}', [IpdPatientController::class, 'paymentSlip'])->name('ipd-patients.payment-slip');

        // Bed Allocations
        Route::get('/{id}/bed-allocations/{allocationId}', [BedAllocationController::class, 'show'])->name('bed-allocations.show');
        Route::get('/{id}/bed-allocations/{allocationId}/edit', [BedAllocationController::class, 'edit'])->name('bed-allocations.edit');
        Route::put('/{id}/bed-allocations/{allocationId}', [BedAllocationController::class, 'update'])->name('bed-allocations.update');

        //Nurse Notes
        Route::get('/{id}/nurse-notes', [NurseNoteController::class, 'create'])->name('nurse-notes');
        Route::post('/{id}/nurse-notes', [NurseNoteController::class, 'store'])->name('nurse-notes.store');
        Route::post('/nurse-notes/{noteId}/reply', [NurseNoteController::class, 'reply'])->name('nurse-notes.reply');

        // Round Doctor
        Route::get('/{id}/round-drs', [RoundDrController::class, 'create'])->name('round-drs');
        Route::post('/{id}/round-drs', [RoundDrController::class, 'store'])->name('round-drs.store');

        // Case Doctor
        Route::get('/{id}/case-drs', [CaseDrController::class, 'create'])->name('case-drs');
        Route::post('/{id}/case-drs', [CaseDrController::class, 'store'])->name('case-drs.store');

        // Surgery Request (OT)
        Route::get('/{id}/surgery-requests', [SurgeryRequestController::class, 'create'])->name('surgery-requests');
        Route::post('/{id}/surgery-requests', [SurgeryRequestController::class, 'store'])->name('surgery-requests.store');

        // Vital Checks
        Route::get('/{id}/vital-checks', [VitalCheckController::class, 'create'])->name('vital-checks');
        Route::post('/{id}/vital-checks', [VitalCheckController::class, 'store'])->name('vital-checks.store');

        // Prescriptions
        Route::get('/{id}/prescriptions/create', [IpdPrescriptionController::class, 'create'])->name('prescriptions.create');
        Route::post('/{id}/prescriptions', [IpdPrescriptionController::class, 'store'])->name('prescriptions.store');
        Route::get('/{id}/prescriptions/{prescriptionId}', [IpdPrescriptionController::class, 'show'])->name('prescriptions.show');
        Route::get('/{id}/prescriptions/{prescriptionId}/pdf', [IpdPrescriptionController::class, 'pdf'])->name('prescriptions.pdf');
        Route::delete('/{id}/prescriptions/{prescriptionId}', [IpdPrescriptionController::class, 'destroy'])->name('prescriptions.destroy');

        // Case Operations
        Route::get('/{id}/case-operations', [CaseOperationController::class, 'create'])->name('case-operations');
        Route::post('/{id}/case-operations', [CaseOperationController::class, 'store'])->name('case-operations.store');
        Route::get('/{id}/case-operations/{operationId}', [CaseOperationController::class, 'show'])->name('case-operations.show');
        Route::get('/{id}/case-operations/{operationId}/edit', [CaseOperationController::class, 'edit'])->name('case-operations.edit');
        Route::put('/{id}/case-operations/{operationId}', [CaseOperationController::class, 'update'])->name('case-operations.update');
        Route::delete('/{id}/case-operations/{operationId}', [CaseOperationController::class, 'destroy'])->name('case-operations.destroy');

        // Medicine Orders
        Route::get('/{id}/medicine-orders/create', [MedicineOrderController::class, 'create'])->name('medicine-orders.create');
        Route::post('/{id}/medicine-orders', [MedicineOrderController::class, 'store'])->name('medicine-orders.store');
        Route::get('/{id}/medicine-orders/{orderId}/edit', [MedicineOrderController::class, 'edit'])->name('medicine-orders.edit');
        Route::put('/{id}/medicine-orders/{orderId}', [MedicineOrderController::class, 'update'])->name('medicine-orders.update');
        Route::delete('/{id}/medicine-orders/{orderId}', [MedicineOrderController::class, 'destroy'])->name('medicine-orders.destroy');

        // Medications
        Route::get('/{id}/medications/create', [IpdMedicationController::class, 'create'])->name('medications.create');
        Route::post('/{id}/medications', [IpdMedicationController::class, 'store'])->name('medications.store');
        Route::delete('/{id}/medications/{medicationId}', [IpdMedicationController::class, 'destroy'])->name('medications.destroy');

        // Unified Lab Order (all 9 lab investigation types · multi-line)
        Route::get('/{id}/lab-orders/create', [\App\Http\Controllers\IPD\LabOrderController::class, 'create'])->name('lab-orders.create');
        Route::post('/{id}/lab-orders', [\App\Http\Controllers\IPD\LabOrderController::class, 'store'])->name('lab-orders.store');

        // Pathology Orders (legacy single-type form, kept for back-compat)
        Route::get('/{id}/pathology-orders', [PathologyOrderController::class, 'create'])->name('pathology-orders');
        Route::post('/{id}/pathology-orders', [PathologyOrderController::class, 'store'])->name('pathology-orders.store');
        Route::get('/{id}/pathology-orders/{orderId}', [PathologyOrderController::class, 'show'])->name('pathology-orders.show');
        Route::get('/{id}/pathology-orders/{orderId}/edit', [PathologyOrderController::class, 'edit'])->name('pathology-orders.edit');
        Route::put('/{id}/pathology-orders/{orderId}', [PathologyOrderController::class, 'update'])->name('pathology-orders.update');
        Route::delete('/{id}/pathology-orders/{orderId}', [PathologyOrderController::class, 'destroy'])->name('pathology-orders.destroy');

        // Radiology Orders
        Route::get('/{id}/radiology-orders', [RadiologyOrderController::class, 'create'])->name('radiology-orders');
        Route::post('/{id}/radiology-orders', [RadiologyOrderController::class, 'store'])->name('radiology-orders.store');
        Route::get('/{id}/radiology-orders/{orderId}', [RadiologyOrderController::class, 'show'])->name('radiology-orders.show');
        Route::get('/{id}/radiology-orders/{orderId}/edit', [RadiologyOrderController::class, 'edit'])->name('radiology-orders.edit');
        Route::put('/{id}/radiology-orders/{orderId}', [RadiologyOrderController::class, 'update'])->name('radiology-orders.update');
        Route::delete('/{id}/radiology-orders/{orderId}', [RadiologyOrderController::class, 'destroy'])->name('radiology-orders.destroy');

        // Charges
        Route::get('/{id}/charges/create', [IpdChargeController::class, 'create'])->name('charges.create');
        Route::post('/{id}/charges', [IpdChargeController::class, 'store'])->name('charges.store');
        Route::get('/{id}/charges/{chargeId}/edit', [IpdChargeController::class, 'edit'])->name('charges.edit');
        Route::put('/{id}/charges/{chargeId}', [IpdChargeController::class, 'update'])->name('charges.update');
        Route::delete('/{id}/charges/{chargeId}', [IpdChargeController::class, 'destroy'])->name('charges.destroy');

        // Payments
        Route::get('/{id}/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/{id}/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('/{id}/payments/{paymentId}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/{id}/payments/{paymentId}', [PaymentController::class, 'update'])->name('payments.update');
        Route::delete('/{id}/payments/{paymentId}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        // Package Enrollment
        Route::get('/{id}/packages/enroll', [IpdPatientController::class, 'enrollPackageForm'])->name('packages.enroll');
        Route::post('/{id}/packages/enroll', [IpdPatientController::class, 'enrollPackageStore'])->name('packages.enroll.store');
        Route::delete('/{id}/packages/{enrollmentId}', [IpdPatientController::class, 'enrollPackageDestroy'])->name('packages.destroy');

        // Treatment Histories
        Route::get('/{id}/treatment-histories/create', [TreatmentHistoryController::class, 'create'])->name('treatment-histories.create');
        Route::post('/{id}/treatment-histories', [TreatmentHistoryController::class, 'store'])->name('treatment-histories.store');
        Route::get('/{id}/treatment-histories/{historyId}/edit', [TreatmentHistoryController::class, 'edit'])->name('treatment-histories.edit');
        Route::put('/{id}/treatment-histories/{historyId}', [TreatmentHistoryController::class, 'update'])->name('treatment-histories.update');
        Route::delete('/{id}/treatment-histories/{historyId}', [TreatmentHistoryController::class, 'destroy'])->name('treatment-histories.destroy');
    });
