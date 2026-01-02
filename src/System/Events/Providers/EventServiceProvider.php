<?php

declare(strict_types=1);

namespace System\Events\Providers;

use Illuminate\Foundation\Events\DiscoverEvents;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;

/**
 * Event service provider for domain events infrastructure.
 *
 * Auto-discovers listeners, subscribers, and observers from domain directories.
 * Uses src/ as base path since Laravel's default discovery doesn't work with our structure.
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        $this->discoverAndRegisterListeners();
        $this->discoverAndRegisterSubscribers();
        $this->discoverAndRegisterObservers();
    }

    /**
     * Disable Laravel's auto-discovery (doesn't work with src/ prefix).
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }

    /**
     * Discover and register event listeners using Laravel's native discovery.
     */
    protected function discoverAndRegisterListeners(): void
    {
        foreach ($this->domainDirectories('Listeners') as $directory) {
            foreach (DiscoverEvents::within($directory, base_path('src')) as $event => $listeners) {
                foreach ($listeners as $listener) {
                    Event::listen($event, $listener);
                }
            }
        }
    }

    /**
     * Discover and register event subscribers.
     */
    protected function discoverAndRegisterSubscribers(): void
    {
        foreach ($this->domainDirectories('Subscribers') as $directory) {
            foreach ($this->classesInDirectory($directory) as $class) {
                Event::subscribe($class);
            }
        }
    }

    /**
     * Discover and register model observers.
     *
     * Convention: UserObserver in Domain\User\Observers observes Domain\User\Models\User
     */
    protected function discoverAndRegisterObservers(): void
    {
        foreach ($this->domainDirectories('Observers') as $directory) {
            foreach ($this->classesInDirectory($directory) as $observerClass) {
                if (preg_match('/^(.+)\\\\Observers\\\\(.+)Observer$/', $observerClass, $matches)) {
                    $modelClass = $matches[1].'\\Models\\'.$matches[2];

                    if (class_exists($modelClass)) {
                        $modelClass::observe($observerClass);
                    }
                }
            }
        }
    }

    /**
     * Get all domain directories for a given subdirectory type.
     *
     * @return array<string>
     */
    protected function domainDirectories(string $subdirectory): array
    {
        $domainPath = base_path('src/Domain');

        if (! is_dir($domainPath)) {
            return [];
        }

        return array_filter(
            array_map(
                fn ($domain) => $domain.'/'.$subdirectory,
                File::directories($domainPath)
            ),
            'is_dir'
        );
    }

    /**
     * Get all class names from PHP files in a directory.
     *
     * @return array<class-string>
     */
    protected function classesInDirectory(string $directory): array
    {
        $classes = [];
        $basePath = base_path('src').'/';

        foreach (File::allFiles($directory) as $file) {
            $class = str_replace(
                ['/', '.php'],
                ['\\', ''],
                str_replace($basePath, '', $file->getRealPath())
            );

            if (class_exists($class)) {
                $classes[] = $class;
            }
        }

        return $classes;
    }
}
