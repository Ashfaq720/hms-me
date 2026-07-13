<?php

use App\Http\Controllers\Pharmacy\CompanyController;
use App\Http\Controllers\Pharmacy\ApprovalController;
use App\Http\Controllers\Pharmacy\ControlledDrugController;
use App\Http\Controllers\Pharmacy\DrugMasterController;
use App\Http\Controllers\Pharmacy\InventoryController;
use App\Http\Controllers\Pharmacy\IpdIssueController;
use App\Http\Controllers\Pharmacy\MedicalGroupController;
use App\Http\Controllers\Pharmacy\MedicineCategoryController;
use App\Http\Controllers\Pharmacy\MedicineController;
use App\Http\Controllers\Pharmacy\MedicineGenericController;
use App\Http\Controllers\Pharmacy\MedicineUnitController;
use App\Http\Controllers\Pharmacy\OpdDispenseController;
use App\Http\Controllers\Pharmacy\PharmacyTransactionController;
use App\Http\Controllers\Pharmacy\PharmacyReturnController;
use App\Http\Controllers\Pharmacy\SupplierController;
use Illuminate\Support\Facades\Route;


Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('medical-groups', MedicalGroupController::class);
    Route::resource('companies', CompanyController::class);
    Route::resource('medicine-units', MedicineUnitController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('medicine-categories', MedicineCategoryController::class);
    Route::resource('medicine-generics', MedicineGenericController::class);
    Route::resource('medicines', MedicineController::class);

    // Pharmacy sub-modules
    Route::get('pharmacy/drug-master', [DrugMasterController::class, 'index'])->name('pharmacy.drug-master');
    Route::get('pharmacy/inventory', [InventoryController::class, 'index'])->name('pharmacy.inventory');
    Route::get('pharmacy/inventory/export', [InventoryController::class, 'export'])->name('pharmacy.inventory.export');
    Route::get('pharmacy/inventory/print', [InventoryController::class, 'print'])->name('pharmacy.inventory.print');
    Route::get('pharmacy/inventory/purchase/create', [InventoryController::class, 'createPurchase'])->name('pharmacy.inventory.purchase.create');
    Route::post('pharmacy/inventory/purchase', [InventoryController::class, 'storePurchase'])->name('pharmacy.inventory.purchase.store');
    Route::get('pharmacy/inventory/{id}/show', [InventoryController::class, 'show'])->name('pharmacy.inventory.show');
    Route::get('pharmacy/opd-dispense', [OpdDispenseController::class, 'index'])->name('pharmacy.opd-dispense');
    Route::get('pharmacy/opd-dispense/create', [OpdDispenseController::class, 'create'])->name('pharmacy.opd-dispense.create');
    Route::post('pharmacy/opd-dispense', [OpdDispenseController::class, 'store'])->name('pharmacy.opd-dispense.store');
    Route::get('pharmacy/opd-dispense/medicine-qty/{medicine}', [OpdDispenseController::class, 'getMedicineQty'])->name('pharmacy.opd-dispense.medicine-qty');
    Route::get('pharmacy/opd-dispense/{id}/show', [OpdDispenseController::class, 'show'])->name('pharmacy.opd-dispense.show');
    Route::get('pharmacy/opd-dispense/export', [OpdDispenseController::class, 'export'])->name('pharmacy.opd-dispense.export');
    Route::get('pharmacy/opd-dispense/print', [OpdDispenseController::class, 'print'])->name('pharmacy.opd-dispense.print');
    Route::get('pharmacy/ipd-issue', [IpdIssueController::class, 'index'])->name('pharmacy.ipd-issue');
    Route::get('pharmacy/ipd-issue/create', [IpdIssueController::class, 'create'])->name('pharmacy.ipd-issue.create');
    Route::post('pharmacy/ipd-issue', [IpdIssueController::class, 'store'])->name('pharmacy.ipd-issue.store');
    Route::get('pharmacy/ipd-issue/medicine-qty/{medicine}', [IpdIssueController::class, 'getMedicineQty'])->name('pharmacy.ipd-issue.medicine-qty');
    Route::get('pharmacy/ipd-issue/export', [IpdIssueController::class, 'export'])->name('pharmacy.ipd-issue.export');
    Route::get('pharmacy/ipd-issue/print', [IpdIssueController::class, 'printList'])->name('pharmacy.ipd-issue.print');
    Route::get('pharmacy/ipd-issue/{id}/show', [IpdIssueController::class, 'show'])->name('pharmacy.ipd-issue.show');
    Route::post('pharmacy/ipd-issue/{id}/approve', [IpdIssueController::class, 'approve'])->name('pharmacy.ipd-issue.approve');
    Route::get('pharmacy/ipd-issue/{id}/print', [IpdIssueController::class, 'printSingle'])->name('pharmacy.ipd-issue.print-single');
    Route::get('pharmacy/approvals', [ApprovalController::class, 'index'])->name('pharmacy.approvals');
    Route::get('pharmacy/approvals/export', [ApprovalController::class, 'export'])->name('pharmacy.approvals.export');
    Route::get('pharmacy/controlled-drugs', [ControlledDrugController::class, 'index'])->name('pharmacy.controlled-drugs');
    Route::get('pharmacy/controlled-drugs/create', [ControlledDrugController::class, 'create'])->name('pharmacy.controlled-drugs.create');
    Route::post('pharmacy/controlled-drugs', [ControlledDrugController::class, 'store'])->name('pharmacy.controlled-drugs.store');
    Route::get('pharmacy/controlled-drugs/export', [ControlledDrugController::class, 'export'])->name('pharmacy.controlled-drugs.export');
    Route::get('pharmacy/controlled-drugs/{id}/show', [ControlledDrugController::class, 'show'])->name('pharmacy.controlled-drugs.show');

    // ── Unified Pharmacy Transactions ──────────────────────────────────────
    Route::get('pharmacy/transactions', [PharmacyTransactionController::class, 'index'])->name('pharmacy.transactions');
    Route::get('pharmacy/transactions/create', [PharmacyTransactionController::class, 'create'])->name('pharmacy.transactions.create');
    Route::post('pharmacy/transactions', [PharmacyTransactionController::class, 'store'])->name('pharmacy.transactions.store');
    Route::get('pharmacy/transactions/export', [PharmacyTransactionController::class, 'export'])->name('pharmacy.transactions.export');
    Route::get('pharmacy/transactions/print', [PharmacyTransactionController::class, 'print'])->name('pharmacy.transactions.print');
    Route::get('pharmacy/transactions/medicine-qty/{medicine}', [PharmacyTransactionController::class, 'getMedicineQty'])->name('pharmacy.transactions.medicine-qty');
    Route::get('pharmacy/transactions/prescription-medicines/{id}', [PharmacyTransactionController::class, 'getPrescriptionMedicines'])->name('pharmacy.transactions.prescription-medicines');
    Route::get('pharmacy/transactions/ipd-medicine-orders/{ipdPatientId}', [PharmacyTransactionController::class, 'getIpdMedicineOrders'])->name('pharmacy.transactions.ipd-medicine-orders');
    Route::get('pharmacy/transactions/{id}/show', [PharmacyTransactionController::class, 'show'])->name('pharmacy.transactions.show');
    Route::post('pharmacy/transactions/{id}/approve', [PharmacyTransactionController::class, 'approve'])->name('pharmacy.transactions.approve');

    // ── Pharmacy Returns ───────────────────────────────────────────────────
    Route::get('pharmacy/returns', [PharmacyReturnController::class, 'index'])->name('pharmacy.returns');
    Route::get('pharmacy/returns/create', [PharmacyReturnController::class, 'create'])->name('pharmacy.returns.create');
    Route::post('pharmacy/returns', [PharmacyReturnController::class, 'store'])->name('pharmacy.returns.store');
    Route::get('pharmacy/returns/transaction-items', [PharmacyReturnController::class, 'getTransactionItems'])->name('pharmacy.returns.transaction-items');
    Route::get('pharmacy/returns/{id}/show', [PharmacyReturnController::class, 'show'])->name('pharmacy.returns.show');
    Route::post('pharmacy/returns/{id}/approve', [PharmacyReturnController::class, 'approve'])->name('pharmacy.returns.approve');
});
