<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [
            __DIR__ . '/../routes/web.php',
            __DIR__ . '/../routes/ambulance_web.php',
            __DIR__ . '/../routes/front_desk.php',
            __DIR__ . '/../routes/ipd.php',
            __DIR__ . '/../routes/opd.php',
            __DIR__ . '/../routes/pharmacy.php',
            __DIR__ . '/../routes/blood_bank.php',
            __DIR__ . '/../routes/pathology.php',
            __DIR__ . '/../routes/radiology.php',
            __DIR__ . '/../routes/billing.php',
            __DIR__ . '/../routes/icu.php',
        ],
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(App\Http\Middleware\CheckPermissions::class);

        // $middleware->alias([
        //     'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
        //     'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        //     'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        // ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
