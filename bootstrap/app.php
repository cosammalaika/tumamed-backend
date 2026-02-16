<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        \App\Console\Commands\SeedRolesCommand::class,
        \App\Console\Commands\CreateAdminCommand::class,
        \App\Console\Commands\ImportHospitalsCommand::class,
    ])
    ->withProviders([
        \App\Providers\AuditServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserRole::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'access-control' => \App\Http\Middleware\EnsureAccessControlPermission::class,
            'no-cache' => \App\Http\Middleware\NoCacheHeaders::class,
            'dashboard-access' => \App\Http\Middleware\EnsureDashboardAccess::class,
            'active-user' => \App\Http\Middleware\EnsureActiveUser::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
