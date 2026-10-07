<?php

use App\Domain\PurchaseOrders\Exceptions\InvalidStatusTransition;
use App\Domain\PurchaseOrders\Exceptions\PurchaseOrderException;
use App\Domain\PurchaseOrders\Exceptions\PurchaseOrderNotFound;
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
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Sanctum SPA auth: requests coming from the Vue app use the session cookie.
        $middleware->statefulApi();
        $middleware->redirectGuestsTo('/login');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Translate domain errors into HTTP responses (the domain itself knows nothing about HTTP).
        $exceptions->render(fn (PurchaseOrderNotFound $e) => response()->json(['message' => $e->getMessage()], 404));
        $exceptions->render(fn (InvalidStatusTransition $e) => response()->json(['message' => $e->getMessage()], 409));
        $exceptions->render(fn (PurchaseOrderException $e) => response()->json(['message' => $e->getMessage()], 422));
    })->create();
