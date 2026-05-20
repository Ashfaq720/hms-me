<?php

use App\Http\Controllers\FrontDesk\FrontDeskController;
use App\Http\Controllers\FrontDesk\FrontDeskLabOrderController;
use App\Http\Controllers\FrontDesk\FrontDeskRegistrationController;
use App\Http\Controllers\FrontDesk\LiveVitalsController;
use App\Http\Controllers\FrontDesk\QuickRegController;
use App\Http\Controllers\FrontDesk\VisitorController;
use App\Http\Controllers\FrontDesk\VitalCheckController;
use App\Http\Controllers\FrontDesk\WalkintokenController;
use Illuminate\Support\Facades\Route;

Route::prefix('front_desk')
    ->middleware(['auth'])
    ->name('front_desk.')
    ->group(function () {

        // /front_desk -> FrontDeskController@index
        Route::get('/', [FrontDeskController::class, 'index'])->name('index');
        Route::get('/new-patient', [FrontDeskController::class, 'new_patient'])->name('new_patient');
        Route::get('/er-registraion', [FrontDeskController::class, 'er_registration'])->name('er_registration');
        Route::post('/er-registraion/store', [FrontDeskController::class, 'er_registration_store'])->name('er_registration.store');
        Route::get('/er-registration/{erPatient}/edit', [FrontDeskController::class, 'er_registration_edit'])->name('er_registration.edit');
        Route::put('/er-registration/{erPatient}', [FrontDeskController::class, 'er_registration_update'])->name('er_registration.update');

        Route::get('/dashboard/today-registration-count', [FrontDeskController::class, 'todayRegistrationCount'])
        ->name('registration.count');
        // Registration (Add New Patient)
        Route::get('/registration/create', [FrontDeskRegistrationController::class, 'create'])->name('registration.create');
        Route::post('/registration', [FrontDeskRegistrationController::class, 'store'])->name('registration.store');
        Route::get('/search-patients', [FrontDeskRegistrationController::class, 'search'])->name('patients.search');
        Route::get('/check-phone', [FrontDeskRegistrationController::class, 'checkPhone'])->name('check.phone');

        // Vital Check
        Route::get('/vitals/create', [VitalCheckController::class, 'create'])->name('vitals.create');
        Route::post('/vitals', [VitalCheckController::class, 'store'])->name('vitals.store');
        Route::get('/vitals/{vital}/edit', [VitalCheckController::class, 'edit'])->name('vitals.edit');
        Route::put('/vitals/{vital}', [VitalCheckController::class, 'update'])->name('vitals.update');

        //Patient Visitor
        Route::get('/visitor/create', [VisitorController::class, 'create'])->name('visitor.create');
        Route::post('/visitor/store', [VisitorController::class, 'store'])->name('visitor.store');
        Route::get('/visitor/patients-by-type', [VisitorController::class, 'patientsByType'])->name('visitor.patients-by-type');
        Route::get('/visitor/today', [VisitorController::class, 'todayVisitors'])->name('visitor.today');
        Route::get('/visitor/{visitor}/slip', [VisitorController::class, 'slip'])->name('visitor.slip');

        //Walk In Token
        Route::get('/walkintoken/create', [WalkintokenController::class, 'create'])->name('walkintoken.create');
        Route::post('/walkintoken', [WalkintokenController::class, 'store'])->name('walkintoken.store');
        Route::get('/walkintoken/{id}/pdf', [WalkintokenController::class, 'pdf'])->name('walkintoken.pdf');
        Route::get('/get-doctors-by-department/{department_id}', [WalkintokenController::class, 'getDoctorsByDepartment'])
            ->name('get.doctors.by.department');
        Route::post('/walkintoken/store', [WalkInTokenController::class, 'store'])
            ->name('front_desk.walkintoken.store');

        // Quick Registration
        Route::get('/quickreg/create', [QuickRegController::class, 'create'])->name('quickreg.create');
        Route::post('/quickreg/store', [QuickRegController::class, 'store'])->name('quickreg.store');

        // Lab Only Orders (front desk creates pathology + radiology directly)
        Route::get('/lab-order/create', [FrontDeskLabOrderController::class, 'create'])->name('lab_order.create');
        Route::post('/lab-order', [FrontDeskLabOrderController::class, 'store'])->name('lab_order.store');
        Route::get('/lab-order/{case_id}/slip', [FrontDeskLabOrderController::class, 'slip'])->name('lab_order.slip');

        // Live Vitals Room
        Route::prefix('live-vitals')->name('live-vitals.')->group(function () {
            Route::get('/',               [LiveVitalsController::class, 'index'])->name('index');
            Route::post('/',              [LiveVitalsController::class, 'store'])->name('store');
            Route::get('/patient-lookup', [LiveVitalsController::class, 'patientLookup'])->name('patient-lookup');
            Route::post('/fetch-machine', [LiveVitalsController::class, 'fetchMachine'])->name('fetch-machine');
        });

    });
