<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Handle CORS globally - must be first
        $middleware->use([
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
        
        // Sanctum middleware for API authentication
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);
        
        // Disable CSRF for API routes
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'storage/*',
        ]);
        
        // Trust all proxies (for development)
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle API exceptions
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Server Error',
                    'error' => app()->environment('local') ? $e->getMessage() : 'Internal Server Error'
                ], 500);
            }
        });
    })->create();
