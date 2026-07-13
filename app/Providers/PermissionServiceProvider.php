<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        
        // Automatically allow 'Super Admin' role to bypass Gate checks (so `can:` middleware respects Super Admin)
        Gate::before(function ($user, $ability) {
            if ($user && method_exists($user, 'hasRole') && $user->hasRole('Super Admin')) {
                return true;
            }

            return null; // fallback to normal checks
        });
        
        // User has exactly ONE specific permission
        Blade::directive('hasOnlyPermission', function ($expression) {
            return "<?php if(auth()->check() && (auth()->user()->hasRole('Super Admin') || auth()->user()->hasPermissionTo({$expression}))): ?>";
        });

        Blade::directive('endHasOnlyPermission', function () {
            return '<?php endif; ?>';
        });

        
        // Permission check with OR operator (||)
        Blade::directive('hasAnyPermission', function ($permissions) {
            return "<?php if(auth()->check() && (auth()->user()->hasRole('Super Admin') || auth()->user()->hasAnyPermission({$permissions}))): ?>";
        });
        
        Blade::directive('endHasAnyPermission', function () {
            return '<?php endif; ?>';
        });

        // Permission check with AND operator (&&)
        Blade::directive('hasAllPermissions', function ($permissions) {
            return "<?php if(auth()->check() && (auth()->user()->hasRole('Super Admin') || auth()->user()->hasAllPermissions({$permissions}))): ?>";
        });
        
        Blade::directive('endHasAllPermissions', function () {
            return '<?php endif; ?>';
        });

        // Role-based permission check
        Blade::directive('hasRole', function ($roles) {
            return "<?php if(auth()->check() && (auth()->user()->hasAnyRole({$roles}))): ?>";
        });
        
        Blade::directive('endHasRole', function () {
            return '<?php endif; ?>';
        });

        // Combined role and permission check
        Blade::directive('hasRoleOrPermission', function ($expression) {
            return "<?php if(auth()->check() && (auth()->user()->hasRole('Super Admin') || auth()->user()->can($expression))): ?>";
        });
        
        Blade::directive('endHasRoleOrPermission', function () {
            return '<?php endif; ?>';
        });
        
        // Check if user is Super Admin
        Blade::directive('isSuperAdmin', function () {
            return "<?php if(auth()->check() && (auth()->user()->hasRole('Super Admin'))): ?>";
        });
        
        Blade::directive('endIsSuperAdmin', function () {
            return '<?php endif; ?>';
        });
    }
}
