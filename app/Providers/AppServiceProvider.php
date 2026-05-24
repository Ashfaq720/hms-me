<?php

namespace App\Providers;

use App\Models\IpdPatient;
use App\Models\IpdPatientBed;
use App\Models\Nicu\NicuProcedure;
use App\Models\Nicu\NicuResourceAllocation;
use App\Models\OpdPatient;
use App\Models\FrontDesk\ErPatient;
use App\Models\Pharmacy\PharmacyTransaction;
use App\Observers\ErPatientObserver;
use App\Observers\IpdPatientBedObserver;
use App\Observers\IpdPatientObserver;
use App\Observers\NicuProcedureObserver;
use App\Observers\NicuResourceAllocationObserver;
use App\Observers\OpdPatientObserver;
use App\Observers\PharmacyTransactionObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The frontend uses Bootstrap 5, so render paginator links with the
        // matching Bootstrap-5 view instead of the default Tailwind template.
        Paginator::useBootstrapFive();

        // Phase 1: hook existing clinical write paths into the new
        // ServiceChargeEngine + StockLedgerService + unified Encounter layer
        // without modifying any existing controller.
        PharmacyTransaction::observe(PharmacyTransactionObserver::class);
        OpdPatient::observe(OpdPatientObserver::class);
        IpdPatient::observe(IpdPatientObserver::class);
        IpdPatientBed::observe(IpdPatientBedObserver::class);

        // NICU module hooks
        NicuResourceAllocation::observe(NicuResourceAllocationObserver::class);
        NicuProcedure::observe(NicuProcedureObserver::class);

        // ER module: auto-open encounter + auto-post registration fee
        ErPatient::observe(ErPatientObserver::class);
    }
}
