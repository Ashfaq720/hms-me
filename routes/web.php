<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DoctorPortalController;
use App\Http\Controllers\PatientPortalController;
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
    // Brute-force protection: max 5 attempts per minute per IP+email
    Route::post('/login', [LoginController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
    Route::post('/register', [RegisterController::class, 'register'])->middleware('throttle:3,1');

    // Step 1: enter email
    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotForm'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendOtp'])->name('password.email');

    // Step 2: verify OTP
    Route::get('/verify-otp', [PasswordResetController::class, 'showOtpForm'])->name('password.otp.form');
    Route::post('/verify-otp', [PasswordResetController::class, 'verifyOtp'])->name('password.otp.verify');
    Route::post('/resend-otp', [PasswordResetController::class, 'resendOtp'])->name('password.otp.resend');

    // Step 3: set new password
    Route::get('/reset-password', [PasswordResetController::class, 'showResetForm'])->name('password.reset');
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

// Dev helper — view current user's roles as JSON (replaces old dd() debug call)
Route::get('/assign-role', function () {
    $user = \Auth::user();
    return response()->json([
        'user' => $user->name ?? '?',
        'email' => $user->email ?? '?',
        'roles' => $user->getRoleNames(),
        'permissions_count' => $user->getAllPermissions()->count(),
    ]);
})->middleware('auth');

// Main Routes
require __DIR__ . '/appointment.php';
require __DIR__ . '/nicu.php';
require __DIR__ . '/er.php';

// Package picker API (used by admission forms)
Route::middleware('auth')->get('/api/packages/applicable',
    [\App\Http\Controllers\Api\PackageApiController::class, 'applicable'])
    ->name('api.packages.applicable');

// Hospital Command Centers — unified landing hubs
Route::middleware('auth')->prefix('admin/center')->name('admin.centers.')->group(function () {
    Route::get('master-data',  [\App\Http\Controllers\Admin\CommandCenterController::class, 'masterData'])->name('master-data');
    Route::get('equipment',    [\App\Http\Controllers\Admin\CommandCenterController::class, 'equipment'])->name('equipment');
    Route::get('inventory',    [\App\Http\Controllers\Admin\CommandCenterController::class, 'inventoryHub'])->name('inventory');
    Route::get('billing',      [\App\Http\Controllers\Admin\CommandCenterController::class, 'billing'])->name('billing');
    Route::get('clinical',     [\App\Http\Controllers\Admin\CommandCenterController::class, 'clinical'])->name('clinical');
});

// Reports Hub · Audit Log · Doctor Wallet
Route::middleware('auth')->prefix('admin/hub')->name('admin.hub.')->group(function () {
    Route::get('reports', [\App\Http\Controllers\Admin\HmsHubController::class, 'reports'])->name('reports');
    Route::get('audit',   [\App\Http\Controllers\Admin\HmsHubController::class, 'audit'])->name('audit');
    Route::get('wallet',  [\App\Http\Controllers\Admin\HmsHubController::class, 'wallet'])->name('wallet');
});

// Package Reports (spec §16)
Route::middleware('auth')->prefix('packages/reports')->name('packages.reports.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Packages\PackageReportsController::class, 'index'])->name('index');
    Route::get('/department', [\App\Http\Controllers\Packages\PackageReportsController::class, 'department'])->name('department');
    Route::get('/utilization', [\App\Http\Controllers\Packages\PackageReportsController::class, 'utilization'])->name('utilization');
    Route::get('/revenue', [\App\Http\Controllers\Packages\PackageReportsController::class, 'revenue'])->name('revenue');
    Route::get('/expiry', [\App\Http\Controllers\Packages\PackageReportsController::class, 'expiry'])->name('expiry');
});

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
Route::resource('/rooms', \App\Http\Controllers\MasterData\RoomController::class)->middleware('auth');

// Doctor Portal — logged-in doctor sees only their own patients
Route::get('/my-patients', [DoctorPortalController::class, 'index'])
    ->middleware('auth')->name('doctor-portal.index');

/*
|--------------------------------------------------------------------------
| Patient Self-Service Portal — separate guard, separate routes
|--------------------------------------------------------------------------
*/
Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('guest:patient')->group(function () {
        Route::get('/login',  [PatientPortalController::class, 'showLogin'])->name('login');
        Route::post('/login', [PatientPortalController::class, 'login'])->middleware('throttle:5,1');
    });

    Route::middleware('auth:patient')->group(function () {
        Route::get('/',              [PatientPortalController::class, 'dashboard'])->name('dashboard');
        Route::get('/bills',         [PatientPortalController::class, 'bills'])->name('bills');
        Route::get('/prescriptions', [PatientPortalController::class, 'prescriptions'])->name('prescriptions');
        Route::get('/profile',       [PatientPortalController::class, 'profile'])->name('profile');
        Route::post('/profile/password', [PatientPortalController::class, 'changePassword'])->name('profile.password');
        Route::post('/logout',       [PatientPortalController::class, 'logout'])->name('logout');
    });
});
Route::resource('/operation-types', OperationTypeController::class)->middleware('auth');
Route::resource('/operations', OperationController::class)->middleware('auth');
Route::resource('/operation-procedures', OperationProcedureController::class)->middleware('auth');
Route::resource('/operation-theatres', OperationTheatreController::class)->middleware('auth');
Route::resource('/departments', DepartmentController::class)->middleware('auth');
// Modal-driven master data — controllers do not implement create/edit/show pages.
Route::resource('/designations', DesignationController::class)->middleware('auth')->except(['create', 'edit', 'show']);
Route::resource('/specialists', SpecialistController::class)->middleware('auth');
Route::resource('/services', ServiceController::class)->middleware('auth');
Route::resource('/doctor-fees', DoctorFeeController::class)->middleware('auth');
Route::resource('/packages', PackageController::class)->middleware('auth');

// Lab Investigation & Symptoms — modal-driven masters; skip page routes the
// controllers never implemented.
Route::resource('/lab-investigation-types', LabInvestigationTypeController::class)->middleware('auth')->except(['create', 'edit', 'show']);
Route::resource('/lab-investigation-categories', LabInvestigationCategoryController::class)->middleware('auth')->except(['create', 'edit', 'show']);
Route::resource('/lab-investigations', LabInvestigationController::class)->middleware('auth')->except(['create', 'edit', 'show']);
Route::resource('/symptoms', SymptomController::class)->middleware('auth')->except(['create', 'edit', 'show']);
Route::middleware(['auth'])->prefix('blood-bank')->name('bb.')->group(function () {
    Route::resource('/blood-groups', BloodGroupController::class)->except(['create', 'edit', 'show']);
    Route::post('blood-groups/{id}/lock', [BloodGroupController::class, 'lock'])->name('blood-groups.lock');
    Route::resource('/components', ComponentController::class)->except(['create', 'edit', 'show']);
    Route::resource('/temperature-rules', ComponentTemperatureRuleController::class)->except(['create', 'edit', 'show']);
    Route::resource('/storage-locations', StorageLocationController::class)->except(['create', 'edit', 'show']);
    Route::resource('/blood-bags', BloodBagController::class)->except(['create', 'edit', 'show']);
    Route::post('blood-bags/{id}/lock', [BloodBagController::class, 'lock'])->name('blood-bags.lock');
    Route::resource('/deferral-reasons', DeferralReasonController::class)->except(['create', 'edit', 'show']);
    Route::post('deferral-reasons/{id}/lock', [DeferralReasonController::class, 'lock'])->name('deferral-reasons.lock');
    Route::resource('/blood-donors', BloodDonorController::class)->except(['create', 'edit', 'show']);
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

// Service Charge module (SRS §5.18)
Route::middleware(['auth'])->prefix('service-charge')->name('service-charge.')->group(function () {
    Route::resource('catalog', \App\Http\Controllers\ServiceCharge\ServiceCatalogController::class)
        ->parameters(['catalog' => 'service_catalog']);
});

// Inventory module (SRS §5.20)
Route::middleware(['auth'])->prefix('inventory')->name('inventory.')->group(function () {
    Route::resource('items', \App\Http\Controllers\Inventory\InventoryItemController::class);
    Route::resource('warehouses', \App\Http\Controllers\Inventory\InventoryWarehouseController::class);
    Route::get('movements', [\App\Http\Controllers\Inventory\StockMovementController::class, 'index'])->name('movements.index');
    Route::get('movements/create', [\App\Http\Controllers\Inventory\StockMovementController::class, 'create'])->name('movements.create');
    Route::post('movements', [\App\Http\Controllers\Inventory\StockMovementController::class, 'store'])->name('movements.store');
    // Stock ledger alias — auditor-facing view of stock_movements
    Route::get('stock-ledger', [\App\Http\Controllers\Inventory\StockMovementController::class, 'index'])->name('stock-ledger');
});

// Organization & Branch (SRS §4.2)
Route::middleware(['auth'])->group(function () {
    Route::resource('organizations', \App\Http\Controllers\Organization\OrganizationController::class)
        ->except(['show']);
    Route::resource('branches', \App\Http\Controllers\Organization\BranchController::class)
        ->except(['show']);
});

// Insurance (SRS §5.21)
Route::middleware(['auth'])->prefix('insurance')->name('insurance.')->group(function () {
    Route::resource('payers', \App\Http\Controllers\Insurance\PayerController::class)->except(['show']);
    Route::get('policies', [\App\Http\Controllers\Insurance\PolicyController::class, 'index'])->name('policies.index');
    Route::get('policies/{policy}', [\App\Http\Controllers\Insurance\PolicyController::class, 'show'])->name('policies.show');

    Route::get('claims', [\App\Http\Controllers\Insurance\ClaimController::class, 'index'])->name('claims.index');
    Route::get('claims/{claim}', [\App\Http\Controllers\Insurance\ClaimController::class, 'show'])->name('claims.show');
    Route::post('claims/build/{bill}', [\App\Http\Controllers\Insurance\ClaimController::class, 'buildFromBill'])->name('claims.build');
    Route::post('claims/{claim}/submit', [\App\Http\Controllers\Insurance\ClaimController::class, 'submit'])->name('claims.submit');
    Route::post('claims/{claim}/approve', [\App\Http\Controllers\Insurance\ClaimController::class, 'approve'])->name('claims.approve');
    Route::post('claims/{claim}/settle', [\App\Http\Controllers\Insurance\ClaimController::class, 'settle'])->name('claims.settle');
    Route::post('claims/{claim}/reject', [\App\Http\Controllers\Insurance\ClaimController::class, 'reject'])->name('claims.reject');
});

// Accounting (SRS §5.22)
Route::middleware(['auth'])->prefix('accounting')->name('accounting.')->group(function () {
    Route::resource('coa', \App\Http\Controllers\Accounting\ChartOfAccountController::class)
        ->parameters(['coa' => 'coa'])
        ->except(['show']);
    Route::get('journal', [\App\Http\Controllers\Accounting\JournalController::class, 'index'])->name('journal.index');
    Route::get('journal/{journal}', [\App\Http\Controllers\Accounting\JournalController::class, 'show'])->name('journal.show');
    Route::get('voucher', [\App\Http\Controllers\Accounting\VoucherController::class, 'index'])->name('voucher.index');
    Route::get('voucher/create', [\App\Http\Controllers\Accounting\VoucherController::class, 'create'])->name('voucher.create');
});

// HR & Payroll (SRS §5.23)
Route::middleware(['auth'])->prefix('hr')->name('hr.')->group(function () {
    Route::resource('employees', \App\Http\Controllers\Hr\EmployeeController::class);
    Route::get('payroll', [\App\Http\Controllers\Hr\PayrollController::class, 'index'])->name('payroll.index');
    Route::get('attendance', [\App\Http\Controllers\Hr\AttendanceController::class, 'index'])->name('attendance.index');
});

// Billing Engine (SRS §5.17 + Prompt 10)
Route::middleware(['auth'])->prefix('billing')->name('billing.')->group(function () {
    // Legacy category billing screens (Emergency / Pathology / Radiology / Utility / Blood Bank)
    Route::get('category/{category}', [\App\Http\Controllers\Billing\LegacyBillingController::class, 'index'])
        ->name('category.index');
    Route::get('category/bill/{bill}/pdf', [\App\Http\Controllers\Billing\LegacyBillingController::class, 'pdf'])
        ->name('category.pdf');
    Route::get('bills', [\App\Http\Controllers\Billing\BillController::class, 'index'])->name('bills.index');
    Route::get('bills/{bill}', [\App\Http\Controllers\Billing\BillController::class, 'show'])->name('bills.show');
    Route::get('bills/{bill}/print', [\App\Http\Controllers\Billing\BillController::class, 'print'])->name('bills.print');
    Route::post('bills/assemble/{encounter}', [\App\Http\Controllers\Billing\BillController::class, 'assembleFromEncounter'])->name('bills.assemble');
    Route::post('bills/{bill}/finalize', [\App\Http\Controllers\Billing\BillController::class, 'finalize'])->name('bills.finalize');
    Route::post('bills/{bill}/payment', [\App\Http\Controllers\Billing\BillController::class, 'collectPayment'])->name('bills.payment');
    Route::post('bills/{bill}/discount', [\App\Http\Controllers\Billing\BillController::class, 'applyDiscount'])->name('bills.discount');
    Route::post('bills/{bill}/cancel', [\App\Http\Controllers\Billing\BillController::class, 'cancel'])->name('bills.cancel');
});

// Insight Dashboard (SRS §5.27)
Route::middleware(['auth'])->get('insight', [\App\Http\Controllers\Insight\InsightDashboardController::class, 'index'])->name('insight.dashboard');

/* ──────────── Simple top-level aliases (no key required) ────────────
   For convenience during dev / first-time setup. Hit these once after
   pulling the project so the storage symlink exists and caches are fresh.
   Lock these down OR remove them once you go to production. */
Route::get('/storage-link', function () {
    \Illuminate\Support\Facades\Artisan::call('storage:link');
    return response('storage:link → ' . trim(\Illuminate\Support\Facades\Artisan::output()))
        ->header('Content-Type', 'text/plain');
})->name('storage-link.alias');

Route::get('/cache-clear', function () {
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    return response('optimize:clear → ' . trim(\Illuminate\Support\Facades\Artisan::output()))
        ->header('Content-Type', 'text/plain');
})->name('cache-clear.alias');

/* ──────────── Maintenance routes (browser-runnable artisan helpers) ────────────
   For environments without CLI access (shared hosting, Heroku, etc.). Each route
   wraps an artisan command and requires ?key=… matching APP_MAINTENANCE_KEY in
   .env. Hit them once after deploy.

   Quick reference:
     /maintenance/storage-link?key=YOUR_KEY   → php artisan storage:link
     /maintenance/cache-clear?key=YOUR_KEY    → php artisan optimize:clear
     /maintenance/migrate?key=YOUR_KEY        → php artisan migrate --force
     /maintenance/seed?key=YOUR_KEY           → php artisan db:seed --force
─────────────────────────────────────────────────────────────────────────────── */
Route::prefix('maintenance')->name('maintenance.')->group(function () {

    $gate = function () {
        $expected = (string) config('app.maintenance_key', env('APP_MAINTENANCE_KEY'));
        abort_unless(
            request('key') && $expected !== '' && hash_equals($expected, (string) request('key')),
            403,
            'Invalid maintenance key. Set APP_MAINTENANCE_KEY in .env and pass ?key=…'
        );
    };

    Route::get('storage-link', function () use ($gate) {
        $gate();
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        return response('storage:link → ' . trim(\Illuminate\Support\Facades\Artisan::output()))
            ->header('Content-Type', 'text/plain');
    })->name('storage-link');

    Route::get('cache-clear', function () use ($gate) {
        $gate();
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        return response('optimize:clear → ' . trim(\Illuminate\Support\Facades\Artisan::output()))
            ->header('Content-Type', 'text/plain');
    })->name('cache-clear');

    Route::get('migrate', function () use ($gate) {
        $gate();
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return response('migrate --force → ' . trim(\Illuminate\Support\Facades\Artisan::output()))
            ->header('Content-Type', 'text/plain');
    })->name('migrate');

    Route::get('seed', function () use ($gate) {
        $gate();
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
        return response('db:seed --force → ' . trim(\Illuminate\Support\Facades\Artisan::output()))
            ->header('Content-Type', 'text/plain');
    })->name('seed');
});
