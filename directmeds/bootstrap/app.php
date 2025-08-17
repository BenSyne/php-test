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
    ->withMiddleware(function (Middleware $middleware): void {
        // Register custom middleware aliases
        $middleware->alias([
            'hipaa.audit' => \App\Http\Middleware\HipaaAuditLog::class,
            'user.status' => \App\Http\Middleware\CheckUserStatus::class,
            'hipaa.acknowledge' => \App\Http\Middleware\RequireHipaaAcknowledgment::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Global middleware
        $middleware->append([
            \App\Http\Middleware\HipaaAuditLog::class,
        ]);

        // Web middleware group
        // Remove CheckUserStatus from global web middleware
        // It should only be applied to authenticated routes

        // API middleware group  
        $middleware->api(append: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
