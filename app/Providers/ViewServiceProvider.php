<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share settings with all views
        View::composer('*', function ($view) {
            $view->with('settings', Setting::class);
        });

        // Share company info with all views
        View::composer('*', function ($view) {
            $companyInfo = Setting::getCompanyInfo();
            $view->with('companyInfo', $companyInfo);
        });
    }
}
