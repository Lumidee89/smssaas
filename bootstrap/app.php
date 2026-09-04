<?php

use App\Http\Middleware\AssignRequestId;
use App\Http\Middleware\RequireFeatureEntitlement;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\SchoolMiddleware;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\IdentifyTenant;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(IdentifyTenant::class);
        $middleware->append([
            AssignRequestId::class,
            SecurityHeaders::class,
        ]);

        $middleware->alias([
            'role' => RoleMiddleware::class,
            'school' => SchoolMiddleware::class,
            'feature' => RequireFeatureEntitlement::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
