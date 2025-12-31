<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use System\View\Exceptions\TwigErrorHandler;
use Twig\Error\Error as TwigError;

$app = (new Application(
    basePath: $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
));

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../src/App/Http/routes.php',
        commands: __DIR__.'/../src/App/Console/commands.php',
        health: '/up',
    )
    ->withCommands([
        __DIR__.'/../src/App/Console/Commands',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            \App\Install\Middleware\RedirectIfNotInstalled::class,
        ]);

        $middleware->validateCsrfTokens(except: [
            'install/finalize',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (TwigError $e, $request) {
            $handler = app(TwigErrorHandler::class);
            $handler->handle($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'error' => config('app.debug') ? $e->getMessage() : 'Template error',
                ], 500);
            }

            return response($handler->render($e), 500);
        });
    })
    ->create()
    ->useConfigPath($app->basePath('src/Config'))
    ->useAppPath($app->basePath('src/App'));
