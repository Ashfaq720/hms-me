<?php

use App\Http\Controllers\ICU\IcuAdmissionController;
use App\Http\Controllers\ICU\IcuAlertController;
use App\Http\Controllers\ICU\IcuAntibioticController;
use App\Http\Controllers\ICU\IcuBillingModeController;
use App\Http\Controllers\ICU\IcuBillingPreviewController;
use App\Http\Controllers\ICU\IcuDischargeController;
use App\Http\Controllers\ICU\IcuDoctorOrderController;
use App\Http\Controllers\ICU\IcuEmergencyController;
use App\Http\Controllers\ICU\IcuEquipmentController;
use App\Http\Controllers\ICU\IcuEquipmentUsageController;
use App\Http\Controllers\ICU\IcuExposureController;
use App\Http\Controllers\ICU\IcuInfectionController;
use App\Http\Controllers\ICU\IcuInfectionReportController;
use App\Http\Controllers\ICU\IcuIntakeOutputController;
use App\Http\Controllers\ICU\IcuMedicineOrderController;
use App\Http\Controllers\ICU\IcuMonitoringDashboardController;
use App\Http\Controllers\ICU\IcuMortalityController;
use App\Http\Controllers\ICU\IcuNursingNoteController;
use App\Http\Controllers\ICU\IcuPackageController;
use App\Http\Controllers\ICU\IcuPathologyOrderController;
use App\Http\Controllers\ICU\IcuProcedureOrderController;
use App\Http\Controllers\ICU\IcuRadiologyOrderController;
use App\Http\Controllers\ICU\IcuTransferController;
use App\Http\Controllers\ICU\IcuVitalThresholdController;
use App\Http\Controllers\ICU\IcuVitalsController;
use Illuminate\Support\Facades\Route;

Route::prefix('icu')
    ->middleware(['auth'])
    ->name('icu.')
    ->group(function () {

        // Admissions
        Route::get('/admissions', [IcuAdmissionController::class, 'index'])->name('admissions.index');
        Route::get('/admissions/create', [IcuAdmissionController::class, 'create'])->name('admissions.create');
        Route::post('/admissions', [IcuAdmissionController::class, 'store'])->name('admissions.store');
        Route::get('/admissions/{id}', [IcuAdmissionController::class, 'show'])->name('admissions.show');

        // AJAX bed lookup for the admission form
        Route::get('/beds/available', [IcuAdmissionController::class, 'availableBeds'])->name('beds.available');

        // Equipment master CRUD
        Route::get('/equipment', [IcuEquipmentController::class, 'index'])->name('equipment.index');
        Route::get('/equipment/create', [IcuEquipmentController::class, 'create'])->name('equipment.create');
        Route::post('/equipment', [IcuEquipmentController::class, 'store'])->name('equipment.store');
        Route::get('/equipment/{id}/edit', [IcuEquipmentController::class, 'edit'])->name('equipment.edit');
        Route::put('/equipment/{id}', [IcuEquipmentController::class, 'update'])->name('equipment.update');
        Route::delete('/equipment/{id}', [IcuEquipmentController::class, 'destroy'])->name('equipment.destroy');
        Route::post('/equipment/{id}/attach-bed', [IcuEquipmentController::class, 'attachToBed'])->name('equipment.attach-bed');
        Route::delete('/equipment/{id}/attach-bed/{bedId}', [IcuEquipmentController::class, 'detachFromBed'])->name('equipment.detach-bed');

        // Equipment usage (per ICU admission)
        Route::get('/admissions/{admissionId}/usage', [IcuEquipmentUsageController::class, 'index'])->name('admissions.usage.index');
        Route::post('/admissions/{admissionId}/usage/assign', [IcuEquipmentUsageController::class, 'assign'])->name('admissions.usage.assign');
        Route::post('/admissions/{admissionId}/usage/{usageId}/remove', [IcuEquipmentUsageController::class, 'remove'])->name('admissions.usage.remove');
        Route::post('/admissions/{admissionId}/usage/{usageId}/change', [IcuEquipmentUsageController::class, 'change'])->name('admissions.usage.change');

        // Cross-admission Order Management dashboard
        Route::get('/orders/manage', [IcuDoctorOrderController::class, 'manage'])->name('orders.manage');

        // Doctor orders (per ICU admission)
        Route::get('/admissions/{admissionId}/orders', [IcuDoctorOrderController::class, 'index'])->name('admissions.orders.index');
        Route::post('/admissions/{admissionId}/orders', [IcuDoctorOrderController::class, 'store'])->name('admissions.orders.store');
        Route::post('/admissions/{admissionId}/orders/{orderId}/acknowledge', [IcuDoctorOrderController::class, 'acknowledge'])->name('admissions.orders.acknowledge');
        Route::post('/admissions/{admissionId}/orders/{orderId}/start', [IcuDoctorOrderController::class, 'start'])->name('admissions.orders.start');
        Route::post('/admissions/{admissionId}/orders/{orderId}/complete', [IcuDoctorOrderController::class, 'complete'])->name('admissions.orders.complete');
        Route::post('/admissions/{admissionId}/orders/{orderId}/cancel', [IcuDoctorOrderController::class, 'cancel'])->name('admissions.orders.cancel');
        Route::post('/admissions/{admissionId}/orders/{orderId}/modify', [IcuDoctorOrderController::class, 'modify'])->name('admissions.orders.modify');
        Route::post('/admissions/{admissionId}/orders/{orderId}/doctor-ack', [IcuDoctorOrderController::class, 'doctorAcknowledge'])->name('admissions.orders.doctor-ack');

        // Nursing notes
        Route::get('/admissions/{admissionId}/nursing-notes', [IcuNursingNoteController::class, 'index'])->name('admissions.nursing-notes.index');
        Route::post('/admissions/{admissionId}/nursing-notes', [IcuNursingNoteController::class, 'store'])->name('admissions.nursing-notes.store');

        // Intake / Output
        Route::get('/admissions/{admissionId}/intake-output', [IcuIntakeOutputController::class, 'index'])->name('admissions.intake-output.index');
        Route::post('/admissions/{admissionId}/intake-output', [IcuIntakeOutputController::class, 'store'])->name('admissions.intake-output.store');

        // Live multi-patient dashboard
        Route::get('/dashboard', [IcuMonitoringDashboardController::class, 'index'])->name('dashboard');

        // Vitals
        Route::get('/admissions/{admissionId}/vitals', [IcuVitalsController::class, 'index'])->name('admissions.vitals.index');
        Route::post('/admissions/{admissionId}/vitals', [IcuVitalsController::class, 'store'])->name('admissions.vitals.store');
        Route::get('/admissions/{admissionId}/vitals/trend', [IcuVitalsController::class, 'trend'])->name('admissions.vitals.trend');

        // Thresholds
        Route::get('/admissions/{admissionId}/thresholds', [IcuVitalThresholdController::class, 'index'])->name('admissions.thresholds.index');
        Route::post('/admissions/{admissionId}/thresholds', [IcuVitalThresholdController::class, 'store'])->name('admissions.thresholds.store');
        Route::delete('/admissions/{admissionId}/thresholds/{vitalType}', [IcuVitalThresholdController::class, 'destroy'])->name('admissions.thresholds.destroy');

        // Alerts
        Route::get('/admissions/{admissionId}/alerts', [IcuAlertController::class, 'index'])->name('admissions.alerts.index');
        Route::post('/admissions/{admissionId}/alerts/{alertId}/acknowledge', [IcuAlertController::class, 'acknowledge'])->name('admissions.alerts.acknowledge');
        Route::post('/admissions/{admissionId}/alerts/{alertId}/close', [IcuAlertController::class, 'close'])->name('admissions.alerts.close');

        // Code Blue / Emergency events
        Route::post('/admissions/{admissionId}/emergency/activate', [IcuEmergencyController::class, 'activate'])->name('admissions.emergency.activate');
        Route::get('/admissions/{admissionId}/emergency/{eventId}', [IcuEmergencyController::class, 'show'])->name('admissions.emergency.show');
        Route::post('/admissions/{admissionId}/emergency/{eventId}/first-response', [IcuEmergencyController::class, 'markFirstResponse'])->name('admissions.emergency.first-response');
        Route::post('/admissions/{admissionId}/emergency/{eventId}/doctor-arrival', [IcuEmergencyController::class, 'markDoctorArrival'])->name('admissions.emergency.doctor-arrival');
        Route::post('/admissions/{admissionId}/emergency/{eventId}/stabilize', [IcuEmergencyController::class, 'markStabilized'])->name('admissions.emergency.stabilize');
        Route::post('/admissions/{admissionId}/emergency/{eventId}/add-action', [IcuEmergencyController::class, 'addAction'])->name('admissions.emergency.add-action');
        Route::post('/admissions/{admissionId}/emergency/{eventId}/close', [IcuEmergencyController::class, 'close'])->name('admissions.emergency.close');

        // Infection / Isolation
        Route::get('/admissions/{admissionId}/infection', [IcuInfectionController::class, 'index'])->name('admissions.infection.index');
        Route::post('/admissions/{admissionId}/infection', [IcuInfectionController::class, 'store'])->name('admissions.infection.store');
        Route::post('/admissions/{admissionId}/infection/{recordId}/resolve', [IcuInfectionController::class, 'resolve'])->name('admissions.infection.resolve');

        // Antibiotic stewardship
        Route::get('/admissions/{admissionId}/antibiotics', [IcuAntibioticController::class, 'index'])->name('admissions.antibiotics.index');
        Route::post('/admissions/{admissionId}/antibiotics', [IcuAntibioticController::class, 'store'])->name('admissions.antibiotics.store');
        Route::post('/admissions/{admissionId}/antibiotics/{logId}/stop', [IcuAntibioticController::class, 'stop'])->name('admissions.antibiotics.stop');

        // Exposure tracking
        Route::get('/admissions/{admissionId}/exposure', [IcuExposureController::class, 'index'])->name('admissions.exposure.index');
        Route::post('/admissions/{admissionId}/exposure', [IcuExposureController::class, 'store'])->name('admissions.exposure.store');

        // Infection control reports (cross-admission)
        Route::get('/infection/reports', [IcuInfectionReportController::class, 'index'])->name('infection.reports');

        // Transfer
        Route::get('/admissions/{admissionId}/transfer', [IcuTransferController::class, 'create'])->name('admissions.transfer.create');
        Route::post('/admissions/{admissionId}/transfer', [IcuTransferController::class, 'store'])->name('admissions.transfer.store');

        // Discharge
        Route::get('/admissions/{admissionId}/discharge', [IcuDischargeController::class, 'create'])->name('admissions.discharge.create');
        Route::post('/admissions/{admissionId}/discharge', [IcuDischargeController::class, 'store'])->name('admissions.discharge.store');
        Route::get('/admissions/{admissionId}/discharge/summary', [IcuDischargeController::class, 'summary'])->name('admissions.discharge.summary');

        // Mortality
        Route::get('/admissions/{admissionId}/mortality', [IcuMortalityController::class, 'show'])->name('admissions.mortality.show');
        Route::get('/admissions/{admissionId}/mortality/create', [IcuMortalityController::class, 'create'])->name('admissions.mortality.create');
        Route::post('/admissions/{admissionId}/mortality', [IcuMortalityController::class, 'store'])->name('admissions.mortality.store');
        Route::post('/admissions/{admissionId}/mortality/review', [IcuMortalityController::class, 'review'])->name('admissions.mortality.review');

        // Packages master
        Route::get('/packages', [IcuPackageController::class, 'index'])->name('packages.index');
        Route::get('/packages/create', [IcuPackageController::class, 'create'])->name('packages.create');
        Route::post('/packages', [IcuPackageController::class, 'store'])->name('packages.store');
        Route::get('/packages/{id}/edit', [IcuPackageController::class, 'edit'])->name('packages.edit');
        Route::put('/packages/{id}', [IcuPackageController::class, 'update'])->name('packages.update');
        Route::delete('/packages/{id}', [IcuPackageController::class, 'destroy'])->name('packages.destroy');
        Route::post('/packages/{id}/items', [IcuPackageController::class, 'addItem'])->name('packages.items.add');
        Route::delete('/packages/{id}/items/{itemId}', [IcuPackageController::class, 'deleteItem'])->name('packages.items.delete');

        // Billing mode (per-admission)
        Route::get('/admissions/{admissionId}/billing/mode', [IcuBillingModeController::class, 'index'])->name('admissions.billing.mode');
        Route::post('/admissions/{admissionId}/billing/mode', [IcuBillingModeController::class, 'apply'])->name('admissions.billing.apply');
        Route::post('/admissions/{admissionId}/billing/mode/end', [IcuBillingModeController::class, 'end'])->name('admissions.billing.end');

        // Bill preview
        Route::get('/admissions/{admissionId}/billing/preview', [IcuBillingPreviewController::class, 'show'])->name('admissions.billing.preview');
        Route::post('/admissions/{admissionId}/billing/refresh', [IcuBillingPreviewController::class, 'refresh'])->name('admissions.billing.refresh');

        // Pathology Orders (per ICU admission)
        Route::get('/admissions/{admissionId}/pathology-orders', [IcuPathologyOrderController::class, 'index'])->name('admissions.pathology-orders.index');
        Route::get('/admissions/{admissionId}/pathology-orders/create', [IcuPathologyOrderController::class, 'create'])->name('admissions.pathology-orders.create');
        Route::post('/admissions/{admissionId}/pathology-orders', [IcuPathologyOrderController::class, 'store'])->name('admissions.pathology-orders.store');
        Route::get('/admissions/{admissionId}/pathology-orders/{orderId}', [IcuPathologyOrderController::class, 'show'])->name('admissions.pathology-orders.show');
        Route::get('/admissions/{admissionId}/pathology-orders/{orderId}/edit', [IcuPathologyOrderController::class, 'edit'])->name('admissions.pathology-orders.edit');
        Route::put('/admissions/{admissionId}/pathology-orders/{orderId}', [IcuPathologyOrderController::class, 'update'])->name('admissions.pathology-orders.update');
        Route::delete('/admissions/{admissionId}/pathology-orders/{orderId}', [IcuPathologyOrderController::class, 'destroy'])->name('admissions.pathology-orders.destroy');

        // Medicine Orders (per ICU admission)
        Route::get('/admissions/{admissionId}/medicine-orders', [IcuMedicineOrderController::class, 'index'])->name('admissions.medicine-orders.index');
        Route::get('/admissions/{admissionId}/medicine-orders/create', [IcuMedicineOrderController::class, 'create'])->name('admissions.medicine-orders.create');
        Route::post('/admissions/{admissionId}/medicine-orders', [IcuMedicineOrderController::class, 'store'])->name('admissions.medicine-orders.store');
        Route::get('/admissions/{admissionId}/medicine-orders/{orderId}/edit', [IcuMedicineOrderController::class, 'edit'])->name('admissions.medicine-orders.edit');
        Route::put('/admissions/{admissionId}/medicine-orders/{orderId}', [IcuMedicineOrderController::class, 'update'])->name('admissions.medicine-orders.update');
        Route::delete('/admissions/{admissionId}/medicine-orders/{orderId}', [IcuMedicineOrderController::class, 'destroy'])->name('admissions.medicine-orders.destroy');

        // Radiology Orders (per ICU admission)
        Route::get('/admissions/{admissionId}/radiology-orders', [IcuRadiologyOrderController::class, 'index'])->name('admissions.radiology-orders.index');
        Route::get('/admissions/{admissionId}/radiology-orders/create', [IcuRadiologyOrderController::class, 'create'])->name('admissions.radiology-orders.create');
        Route::post('/admissions/{admissionId}/radiology-orders', [IcuRadiologyOrderController::class, 'store'])->name('admissions.radiology-orders.store');
        Route::get('/admissions/{admissionId}/radiology-orders/{orderId}', [IcuRadiologyOrderController::class, 'show'])->name('admissions.radiology-orders.show');
        Route::get('/admissions/{admissionId}/radiology-orders/{orderId}/edit', [IcuRadiologyOrderController::class, 'edit'])->name('admissions.radiology-orders.edit');
        Route::put('/admissions/{admissionId}/radiology-orders/{orderId}', [IcuRadiologyOrderController::class, 'update'])->name('admissions.radiology-orders.update');
        Route::delete('/admissions/{admissionId}/radiology-orders/{orderId}', [IcuRadiologyOrderController::class, 'destroy'])->name('admissions.radiology-orders.destroy');

        // Procedure Orders (per ICU admission)
        Route::get('/admissions/{admissionId}/procedure-orders', [IcuProcedureOrderController::class, 'index'])->name('admissions.procedure-orders.index');
        Route::post('/admissions/{admissionId}/procedure-orders', [IcuProcedureOrderController::class, 'store'])->name('admissions.procedure-orders.store');
        Route::get('/admissions/{admissionId}/procedure-orders/{orderId}/edit', [IcuProcedureOrderController::class, 'edit'])->name('admissions.procedure-orders.edit');
        Route::put('/admissions/{admissionId}/procedure-orders/{orderId}', [IcuProcedureOrderController::class, 'update'])->name('admissions.procedure-orders.update');
        Route::delete('/admissions/{admissionId}/procedure-orders/{orderId}', [IcuProcedureOrderController::class, 'destroy'])->name('admissions.procedure-orders.destroy');
    });
