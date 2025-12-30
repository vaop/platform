<?php

declare(strict_types=1);

namespace System\View\Exceptions;

use Illuminate\Support\Facades\Log;
use Twig\Error\Error as TwigError;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

/**
 * Handles Twig template errors with proper logging and user-friendly responses.
 *
 * In development mode, detailed error information is shown including the
 * template name, line number, and stack trace. In production, a generic
 * error page is displayed while the details are logged.
 */
class TwigErrorHandler
{
    /**
     * Log the Twig error with contextual information.
     */
    public function handle(TwigError $exception): void
    {
        $context = [
            'template' => $exception->getSourceContext()?->getName() ?? 'unknown',
            'line' => $exception->getTemplateLine(),
            'message' => $exception->getRawMessage(),
            'type' => $this->getErrorType($exception),
        ];

        Log::error('Twig template error: '.$exception->getRawMessage(), $context);
    }

    /**
     * Render the error for display.
     */
    public function render(TwigError $exception): string
    {
        if (config('app.debug')) {
            return $this->renderDebug($exception);
        }

        return $this->renderProduction();
    }

    /**
     * Get the error type string for logging/display.
     */
    private function getErrorType(TwigError $exception): string
    {
        return match (true) {
            $exception instanceof SyntaxError => 'syntax',
            $exception instanceof LoaderError => 'loader',
            $exception instanceof RuntimeError => 'runtime',
            default => 'unknown',
        };
    }

    /**
     * Render detailed error for development mode.
     */
    private function renderDebug(TwigError $exception): string
    {
        $type = ucfirst($this->getErrorType($exception));
        $template = htmlspecialchars($exception->getSourceContext()?->getName() ?? 'unknown');
        $line = $exception->getTemplateLine();
        $message = htmlspecialchars($exception->getRawMessage());
        $trace = htmlspecialchars($exception->getTraceAsString());

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Template {$type} Error</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; padding: 2rem; }
        .container { max-width: 900px; margin: 0 auto; }
        .error-card { background: white; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden; }
        .error-header { background: #dc2626; color: white; padding: 1rem 1.5rem; }
        .error-header h1 { font-size: 1.25rem; font-weight: 600; }
        .error-body { padding: 1.5rem; }
        .error-meta { display: grid; grid-template-columns: 120px 1fr; gap: 0.5rem; margin-bottom: 1.5rem; }
        .error-meta dt { font-weight: 600; color: #64748b; }
        .error-meta dd { color: #1e293b; }
        .error-message { background: #fef2f2; border: 1px solid #fecaca; border-radius: 4px; padding: 1rem; margin-bottom: 1.5rem; }
        .error-message code { font-family: ui-monospace, monospace; color: #dc2626; }
        .error-trace { background: #1e293b; color: #e2e8f0; border-radius: 4px; padding: 1rem; overflow-x: auto; }
        .error-trace pre { font-family: ui-monospace, monospace; font-size: 0.875rem; white-space: pre-wrap; word-break: break-all; }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-card">
            <div class="error-header">
                <h1>Template {$type} Error</h1>
            </div>
            <div class="error-body">
                <dl class="error-meta">
                    <dt>Template:</dt>
                    <dd>{$template}</dd>
                    <dt>Line:</dt>
                    <dd>{$line}</dd>
                </dl>
                <div class="error-message">
                    <code>{$message}</code>
                </div>
                <div class="error-trace">
                    <pre>{$trace}</pre>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Render generic error for production mode.
     */
    private function renderProduction(): string
    {
        // Try to use the 500 error view if available
        try {
            return view('errors.500')->render();
        } catch (\Throwable) {
            // Fallback to inline HTML if no error view exists
            return <<<'HTML'
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Error</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui, -apple-system, sans-serif; background: #f8fafc; display: flex; align-items: center; justify-content: center; min-height: 100vh; }
        .error { text-align: center; }
        .error h1 { font-size: 4rem; font-weight: 700; color: #cbd5e1; }
        .error h2 { font-size: 1.5rem; font-weight: 600; color: #1e293b; margin-top: 1rem; }
        .error p { color: #64748b; margin-top: 0.5rem; }
        .error a { display: inline-block; margin-top: 1.5rem; padding: 0.75rem 1.5rem; background: #3b82f6; color: white; text-decoration: none; border-radius: 6px; }
        .error a:hover { background: #2563eb; }
    </style>
</head>
<body>
    <div class="error">
        <h1>500</h1>
        <h2>Server Error</h2>
        <p>Something went wrong. Please try again later.</p>
        <a href="/">Go Home</a>
    </div>
</body>
</html>
HTML;
        }
    }
}
