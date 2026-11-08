<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Throwable $e) {
            if (isset($_GET['debug'])) {
                return response()->json([
                    'error' => $e->getMessage(),
                    'class' => get_class($e),
                    'file' => $e->getFile() . ':' . $e->getLine(),
                    'previous' => $e->getPrevious() ? $e->getPrevious()->getMessage() : null,
                    'trace' => array_slice(explode("\n", $e->getTraceAsString()), 0, 10),
                ], 500);
            }
        });
    })->create();

if (isset($_ENV['LARAVEL_STORAGE_PATH'])) {
    $app->useStoragePath($_ENV['LARAVEL_STORAGE_PATH']);
}

return $app;
