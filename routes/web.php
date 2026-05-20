<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Charges\ChargeCategoryController;
use App\Http\Controllers\Charges\ChargeTypeController;
use App\Http\Controllers\Charges\HospitalChargeController;
use App\Http\Controllers\Charges\TaxCategoryController;
use App\Http\Controllers\Charges\UniteTypeController;
use App\Http\Controllers\HealthCardController;
use App\Http\Controllers\MasterData\BedController;
use App\Http\Controllers\MasterData\BedGroupController;
use App\Http\Controllers\MasterData\BedTypeController;
use App\Http\Controllers\MasterData\BloodBank\BloodBagController;
use App\Http\Controllers\MasterData\BloodBank\BloodDonorController;
use App\Http\Controllers\MasterData\BloodBank\BloodGroupController;
use App\Http\Controllers\MasterData\BloodBank\ComponentController;
use App\Http\Controllers\MasterData\BloodBank\ComponentTemperatureRuleController;
use App\Http\Controllers\MasterData\BloodBank\DeferralReasonController;
use App\Http\Controllers\MasterData\BloodBank\StorageLocationController;
use App\Http\Controllers\MasterData\DepartmentController;
use App\Http\Controllers\MasterData\DesignationController;
use App\Http\Controllers\MasterData\DoctorController;
use App\Http\Controllers\MasterData\DoctorFeeController;
use App\Http\Controllers\MasterData\FloorController;
use App\Http\Controllers\MasterData\LabInvestigationCategoryController;
use App\Http\Controllers\MasterData\LabInvestigationController;
use App\Http\Controllers\MasterData\LabInvestigationTypeController;
use App\Http\Controllers\MasterData\OperationController;
use App\Http\Controllers\MasterData\OperationProcedureController;
use App\Http\Controllers\MasterData\OperationTheatreController;
use App\Http\Controllers\MasterData\OperationTypeController;
use App\Http\Controllers\MasterData\PackageController;
use App\Http\Controllers\MasterData\PatientController;
use App\Http\Controllers\MasterData\ServiceController;
use App\Http\Controllers\MasterData\SymptomController;
use App\Http\Controllers\OpdPatientDepartmentController;
use App\Http\Controllers\SpecialistController;
use Illuminate\Support\Facades\Route;

// Home route with authentication check
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
})->name('home');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'reset'])->name('password.update');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

Route::group([
    'middleware' =>
    ['auth', 'throttle:60,1'],
    'prefix'     => 'admin',
], function () {

    // Protected Routes
    require __DIR__ . '/modules/v1/users/user.php';
    require __DIR__ . '/modules/v1/setting.php';
});

// check roles and permissions
Route::get('/assign-role', function () {
    $user = \Auth::user();
    // $user->removeRole('CRM');
    // $user->removeRole('company');
    // $user->assignRole('crm');
    dd($user->getRoleNames());
})->middleware('auth');

// Main Routes
require __DIR__ . '/appointment.php';

//Master Data
Route::get('/patients/datatable', [PatientController::class, 'datatable'])->middleware('auth')->name('patients.datatable');
Route::resource('/patients', PatientController::class)->middleware('auth');
Route::get('/api/patients/{patient}', [PatientController::class, 'show'])->middleware('auth')->name('api.patients.show');

// Health Card
Route::middleware('auth')->prefix('health-card')->name('health-card.')->group(function () {
    Route::get('/',                  [HealthCardController::class, 'index'])->name('index');
    Route::get('find/by-card-no',   [HealthCardController::class, 'findByCard'])->name('find');
    Route::get('check-followup',    [HealthCardController::class, 'checkFollowUp'])->name('check-followup');
    Route::post('checkin',          [HealthCardController::class, 'checkin'])->name('checkin');
    Route::get('{patient}',         [HealthCardController::class, 'show'])->name('show');
});
Route::resource('/doctors', DoctorController::class)->middleware('auth');
Route::resource('/floors', FloorController::class)->middleware('auth');
Route::resource('/bed-groups', BedGroupController::class)->middleware('auth');
Route::resource('/bed-types', BedTypeController::class)->middleware('auth');
Route::resource('/beds', BedController::class)->middleware('auth');
Route::resource('/operation-types', OperationTypeController::class)->middleware('auth');
Route::resource('/operations', OperationController::class)->middleware('auth');
Route::resource('/operation-procedures', OperationProcedureController::class)->middleware('auth');
Route::resource('/operation-theatres', OperationTheatreController::class)->middleware('auth');
Route::resource('/departments', DepartmentController::class)->middleware('auth');
Route::resource('/designations', DesignationController::class)->middleware('auth');
Route::resource('/specialists', SpecialistController::class)->middleware('auth');
Route::resource('/services', ServiceController::class)->middleware('auth');
Route::resource('/doctor-fees', DoctorFeeController::class)->middleware('auth');
Route::resource('/packages', PackageController::class)->middleware('auth');

//Lab Investigation & Symptoms
Route::resource('/lab-investigation-types', LabInvestigationTypeController::class)->middleware('auth');
Route::resource('/lab-investigation-categories', LabInvestigationCategoryController::class)->middleware('auth');
Route::resource('/lab-investigations', LabInvestigationController::class)->middleware('auth');
Route::resource('/symptoms', SymptomController::class)->middleware('auth');
Route::middleware(['auth'])->prefix('blood-bank')->name('bb.')->group(function () {
    Route::resource('/blood-groups', BloodGroupController::class);
    Route::post('blood-groups/{id}/lock', [BloodGroupController::class, 'lock'])->name('blood-groups.lock');
    Route::resource('/components', ComponentController::class);
    Route::resource('/temperature-rules', ComponentTemperatureRuleController::class);
    Route::resource('/storage-locations', StorageLocationController::class);
    Route::resource('/blood-bags', BloodBagController::class);
    Route::post('blood-bags/{id}/lock', [BloodBagController::class, 'lock'])->name('blood-bags.lock');
    Route::resource('/deferral-reasons', DeferralReasonController::class);
    Route::post('deferral-reasons/{id}/lock', [DeferralReasonController::class, 'lock'])->name('deferral-reasons.lock');
    Route::resource('/blood-donors', BloodDonorController::class);
});

Route::resource('opd-patient-departments', controller: OpdPatientDepartmentController::class);

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('charges', HospitalChargeController::class);
    Route::resource('unite-types', UniteTypeController::class);
    Route::resource('tax-categories', TaxCategoryController::class);
    Route::resource('charge-types', ChargeTypeController::class);
    Route::resource('charge-categories', ChargeCategoryController::class);
});
Route::get('charges/get-charge-categories', [HospitalChargeController::class, 'getChargeCategories'])
    ->name('admin.charges.get-charge-categories');

Route::get('/get-charges-by-category/{categoryId}', [HospitalChargeController::class, 'getChargesByCategory'])
    ->name('charges.by.category');

Route::get('/get-charge-details/{chargeId}', [HospitalChargeController::class, 'getChargeDetails'])
    ->name('charges.details');
