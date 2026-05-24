<?php

use App\Http\Controllers\OT\OtDashboardController;
use App\Http\Controllers\OT\SurgeryRequestController;
use App\Http\Controllers\OT\SurgeryScheduleController;
use App\Http\Controllers\OT\PreOpController;
use App\Http\Controllers\OT\OtTransferController;
use App\Http\Controllers\OT\OtRoomResourceController;
use App\Http\Controllers\OT\OtTeamController;
use App\Http\Controllers\OT\AnesthesiaController;
use App\Http\Controllers\OT\IntraOpController;
use App\Http\Controllers\OT\OtConsumableUsageController;
use App\Http\Controllers\OT\PostOpController;
use App\Http\Controllers\OT\PacuController;
use App\Http\Controllers\OT\OtBillingController;
use App\Http\Controllers\OT\OtInventoryController;
use App\Http\Controllers\OT\OtCleaningController;
use App\Http\Controllers\OT\OtDocumentController;
use App\Http\Controllers\OT\EmergencyOtController;
use App\Http\Controllers\OT\OtReportController;
use App\Http\Controllers\OT\OtSetupController;
use Illuminate\Support\Facades\Route;

Route::prefix('ot')
    ->middleware(['auth'])
    ->name('ot.')
    ->group(function () {

        // 1. OT Dashboard
        Route::get('/', [OtDashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard', [OtDashboardController::class, 'index'])->name('dashboard.index');
        Route::post('/notifications/{id}/read', [OtDashboardController::class, 'markNotificationRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [OtDashboardController::class, 'markAllNotificationsRead'])->name('notifications.read-all');

        // 2. Surgery Request / OT Booking
        Route::resource('surgery-requests', SurgeryRequestController::class);
        Route::post('surgery-requests/{id}/submit', [SurgeryRequestController::class, 'submit'])->name('surgery-requests.submit');
        Route::post('surgery-requests/{id}/review', [SurgeryRequestController::class, 'review'])->name('surgery-requests.review');
        Route::post('surgery-requests/{id}/accept', [SurgeryRequestController::class, 'accept'])->name('surgery-requests.accept');
        Route::post('surgery-requests/{id}/reject', [SurgeryRequestController::class, 'reject'])->name('surgery-requests.reject');
        Route::post('surgery-requests/{id}/send-back', [SurgeryRequestController::class, 'sendBack'])->name('surgery-requests.send-back');
        Route::post('surgery-requests/{id}/pending-info', [SurgeryRequestController::class, 'pendingInfo'])->name('surgery-requests.pending-info');
        Route::post('surgery-requests/{id}/fast-track', [SurgeryRequestController::class, 'fastTrack'])->name('surgery-requests.fast-track');
        Route::post('surgery-requests/{id}/junior-approve', [SurgeryRequestController::class, 'juniorApprove'])->name('surgery-requests.junior-approve');
        Route::post('surgery-requests/{id}/consultant-approve', [SurgeryRequestController::class, 'consultantApprove'])->name('surgery-requests.consultant-approve');
        Route::post('surgery-requests/{id}/move-to-scheduling', [SurgeryRequestController::class, 'moveToScheduling'])->name('surgery-requests.move-to-scheduling');
        Route::post('surgery-requests/{id}/cancel', [SurgeryRequestController::class, 'cancel'])->name('surgery-requests.cancel');

        // 3. Surgery Scheduling
        Route::resource('schedules', SurgeryScheduleController::class);
        Route::get('schedules-calendar', [SurgeryScheduleController::class, 'calendar'])->name('schedules.calendar');
        Route::get('schedules-availability', [SurgeryScheduleController::class, 'availability'])->name('schedules.availability');
        Route::post('schedules/{id}/reschedule', [SurgeryScheduleController::class, 'reschedule'])->name('schedules.reschedule');
        Route::post('schedules/{id}/cancel', [SurgeryScheduleController::class, 'cancel'])->name('schedules.cancel');
        Route::post('schedules/{id}/approve', [SurgeryScheduleController::class, 'approve'])->name('schedules.approve');
        Route::post('schedules/{id}/status', [SurgeryScheduleController::class, 'updateStatus'])->name('schedules.status');

        // 4. Pre-Operative Management
        Route::get('pre-op', [PreOpController::class, 'index'])->name('pre-op.index');
        Route::get('pre-op/{scheduleId}', [PreOpController::class, 'show'])->name('pre-op.show');
        Route::put('pre-op/{scheduleId}', [PreOpController::class, 'update'])->name('pre-op.update');
        Route::post('pre-op/{scheduleId}/complete', [PreOpController::class, 'complete'])->name('pre-op.complete');
        Route::post('pre-op/{scheduleId}/override', [PreOpController::class, 'emergencyOverride'])->name('pre-op.override');

        // 5. OT Patient Transfer
        Route::get('transfers', [OtTransferController::class, 'index'])->name('transfers.index');
        Route::post('transfers/{scheduleId}/initiate', [OtTransferController::class, 'initiate'])->name('transfers.initiate');
        Route::post('transfers/{transferId}/arrive', [OtTransferController::class, 'arrive'])->name('transfers.arrive');

        // 6. OT Room & Resource Management
        Route::resource('rooms', OtRoomResourceController::class);
        Route::resource('equipments', OtRoomResourceController::class, ['as' => 'equipments-alt']);
        Route::get('room-status', [OtRoomResourceController::class, 'roomStatus'])->name('rooms.status');

        // 7. OT Team Management
        Route::get('teams', [OtTeamController::class, 'index'])->name('teams.index');
        Route::get('teams/{scheduleId}', [OtTeamController::class, 'show'])->name('teams.show');
        Route::post('teams/{scheduleId}/assign', [OtTeamController::class, 'assign'])->name('teams.assign');
        Route::delete('teams/members/{memberId}', [OtTeamController::class, 'remove'])->name('teams.remove');

        // 8. Anesthesia Management
        Route::get('anesthesia', [AnesthesiaController::class, 'index'])->name('anesthesia.index');
        Route::get('anesthesia/{scheduleId}', [AnesthesiaController::class, 'show'])->name('anesthesia.show');
        Route::post('anesthesia/{scheduleId}', [AnesthesiaController::class, 'store'])->name('anesthesia.store');
        Route::put('anesthesia/{scheduleId}', [AnesthesiaController::class, 'update'])->name('anesthesia.update');
        Route::post('anesthesia/{scheduleId}/start', [AnesthesiaController::class, 'start'])->name('anesthesia.start');

        // 9. Surgery Execution / Intra-Operative Record
        Route::get('intra-op', [IntraOpController::class, 'index'])->name('intra-op.index');
        Route::get('intra-op/{scheduleId}', [IntraOpController::class, 'show'])->name('intra-op.show');
        Route::post('intra-op/{scheduleId}/start', [IntraOpController::class, 'startSurgery'])->name('intra-op.start');
        Route::post('intra-op/{scheduleId}/complete', [IntraOpController::class, 'completeSurgery'])->name('intra-op.complete');
        Route::put('intra-op/{scheduleId}', [IntraOpController::class, 'update'])->name('intra-op.update');

        // 10. OT Consumables & Instrument Usage
        Route::get('consumables', [OtConsumableUsageController::class, 'index'])->name('consumables.index');
        Route::get('consumables/{scheduleId}', [OtConsumableUsageController::class, 'show'])->name('consumables.show');
        Route::post('consumables/{scheduleId}', [OtConsumableUsageController::class, 'store'])->name('consumables.store');
        Route::delete('consumables/{id}', [OtConsumableUsageController::class, 'destroy'])->name('consumables.destroy');

        // 11. Post-Operative Management
        Route::get('post-op', [PostOpController::class, 'index'])->name('post-op.index');
        Route::get('post-op/{scheduleId}', [PostOpController::class, 'show'])->name('post-op.show');
        Route::post('post-op/{scheduleId}', [PostOpController::class, 'store'])->name('post-op.store');
        Route::put('post-op/{scheduleId}', [PostOpController::class, 'update'])->name('post-op.update');

        // 12. Recovery / PACU Management
        Route::get('pacu', [PacuController::class, 'index'])->name('pacu.index');
        Route::get('pacu/{scheduleId}', [PacuController::class, 'show'])->name('pacu.show');
        Route::post('pacu/{scheduleId}/admit', [PacuController::class, 'admit'])->name('pacu.admit');
        Route::post('pacu/{id}/vitals', [PacuController::class, 'addVitals'])->name('pacu.vitals');
        Route::post('pacu/{id}/clear-recovery', [PacuController::class, 'clearRecovery'])->name('pacu.clear-recovery');
        Route::post('pacu/{id}/discharge', [PacuController::class, 'discharge'])->name('pacu.discharge');

        // 13. OT Billing Integration
        Route::get('billing', [OtBillingController::class, 'index'])->name('billing.index');
        Route::get('billing/{scheduleId}', [OtBillingController::class, 'show'])->name('billing.show');
        Route::post('billing/{scheduleId}/post', [OtBillingController::class, 'postCharges'])->name('billing.post');
        Route::get('billing/{scheduleId}/print', [OtBillingController::class, 'print'])->name('billing.print');
        Route::get('billing/{scheduleId}/pdf', [OtBillingController::class, 'pdf'])->name('billing.pdf');

        // 14. OT Inventory Integration
        Route::get('inventory', [OtInventoryController::class, 'index'])->name('inventory.index');
        Route::post('inventory/{usageId}/deduct', [OtInventoryController::class, 'deduct'])->name('inventory.deduct');

        // 15. Cleaning & Sterilization
        Route::get('cleaning', [OtCleaningController::class, 'index'])->name('cleaning.index');
        Route::post('cleaning/{roomId}/start', [OtCleaningController::class, 'start'])->name('cleaning.start');
        Route::post('cleaning/{logId}/complete', [OtCleaningController::class, 'complete'])->name('cleaning.complete');

        // 16. OT Documents & Consent
        Route::get('documents', [OtDocumentController::class, 'index'])->name('documents.index');
        Route::post('documents', [OtDocumentController::class, 'store'])->name('documents.store');
        Route::get('documents/{id}/download', [OtDocumentController::class, 'download'])->name('documents.download');
        Route::delete('documents/{id}', [OtDocumentController::class, 'destroy'])->name('documents.destroy');
        Route::post('documents/{id}/sign', [OtDocumentController::class, 'sign'])->name('documents.sign');

        // 17. Emergency OT Management
        Route::get('emergency', [EmergencyOtController::class, 'index'])->name('emergency.index');
        Route::get('emergency/create', [EmergencyOtController::class, 'create'])->name('emergency.create');
        Route::post('emergency', [EmergencyOtController::class, 'store'])->name('emergency.store');
        Route::post('emergency/{id}/approve', [EmergencyOtController::class, 'approve'])->name('emergency.approve');

        // 18. Reports & Analytics
        Route::get('reports', [OtReportController::class, 'index'])->name('reports.index');
        Route::get('reports/surgeries', [OtReportController::class, 'surgeryReport'])->name('reports.surgeries');
        Route::get('reports/utilization', [OtReportController::class, 'utilization'])->name('reports.utilization');
        Route::get('reports/cancellations', [OtReportController::class, 'cancellations'])->name('reports.cancellations');
        Route::get('reports/consumables', [OtReportController::class, 'consumables'])->name('reports.consumables');
        Route::get('reports/revenue', [OtReportController::class, 'revenue'])->name('reports.revenue');
        Route::get('reports/audit', [OtReportController::class, 'auditTrail'])->name('reports.audit');

        // 19. OT Setup / Master Configuration
        Route::get('setup', [OtSetupController::class, 'index'])->name('setup.index');
        Route::resource('setup/rooms', \App\Http\Controllers\OT\Setup\OtRoomController::class, ['as' => 'setup']);
        Route::resource('setup/equipments', \App\Http\Controllers\OT\Setup\OtEquipmentController::class, ['as' => 'setup']);
        Route::resource('setup/surgery-categories', \App\Http\Controllers\OT\Setup\OtSurgeryCategoryController::class, ['as' => 'setup']);
        Route::resource('setup/anesthesia-types', \App\Http\Controllers\OT\Setup\OtAnesthesiaTypeController::class, ['as' => 'setup']);
        Route::resource('setup/surgery-types', \App\Http\Controllers\OT\Setup\OtSurgeryTypeController::class, ['as' => 'setup']);
        Route::resource('setup/consumables', \App\Http\Controllers\OT\Setup\OtConsumableController::class, ['as' => 'setup']);
        Route::get('setup/checklists', [\App\Http\Controllers\OT\Setup\OtChecklistController::class, 'index'])->name('setup.checklists.index');
        Route::get('setup/checklists/create', [\App\Http\Controllers\OT\Setup\OtChecklistController::class, 'create'])->name('setup.checklists.create');
    });
