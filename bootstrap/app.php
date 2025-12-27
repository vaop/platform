<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$app = (new Application(
    basePath: $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
));

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../src/App/Http/routes.php',
        commands: __DIR__ . '/../src/App/Console/commands.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create()
    ->useAppPath($app->basePath('src/App'));
