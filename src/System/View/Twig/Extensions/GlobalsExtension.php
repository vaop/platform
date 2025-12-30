<?php

declare(strict_types=1);

namespace System\View\Twig\Extensions;

use Illuminate\Http\Request;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

/**
 * Provides global variables available in all Twig templates.
 */
class GlobalsExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly Request $request
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        return [
            'app' => [
                'name' => config('app.name'),
                'url' => config('app.url'),
                'locale' => app()->getLocale(),
                'debug' => config('app.debug'),
                'env' => app()->environment(),
            ],
            'request' => [
                'path' => $this->request->path(),
                'url' => $this->request->url(),
                'fullUrl' => $this->request->fullUrl(),
                'method' => $this->request->method(),
                'is_secure' => $this->request->secure(),
            ],
        ];
    }
}
