<?php

declare(strict_types=1);

namespace System\Events\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use ReflectionClass;

/**
 * Event service provider for domain events infrastructure.
 *
 * This provider sets up the event infrastructure for the application.
 * It auto-discovers listeners, subscribers, and observers within domain directories.
 *
 * Auto-discovery paths:
 *   - src/Domain/* /Listeners - Event listeners
 *   - src/Domain/* /Subscribers - Event subscribers
 *   - src/Domain/* /Observers - Model observers
 */
class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // Auto-discovered from src/Domain/*/Listeners
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array<int, class-string>
     */
    protected $subscribe = [
        // Auto-discovered from src/Domain/*/Subscribers
    ];

    /**
     * The model observers for your application.
     *
     * @var array<class-string, array<int, class-string>|class-string>
     */
    protected $observers = [
        // Auto-discovered from src/Domain/*/Observers
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        parent::boot();

        $this->discoverAndRegisterSubscribers();
        $this->discoverAndRegisterObservers();
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return true;
    }

    /**
     * Get the listener directories that should be used to discover events.
     *
     * @return array<int, string>
     */
    protected function discoverEventsWithin(): array
    {
        return $this->getDomainDirectories('Listeners');
    }

    /**
     * Discover and register event subscribers from domain directories.
     */
    protected function discoverAndRegisterSubscribers(): void
    {
        foreach ($this->getDomainDirectories('Subscribers') as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            foreach (File::allFiles($directory) as $file) {
                $class = $this->getClassFromFile($file->getPathname());

                if ($class && class_exists($class)) {
                    Event::subscribe($class);
                }
            }
        }
    }

    /**
     * Discover and register model observers from domain directories.
     */
    protected function discoverAndRegisterObservers(): void
    {
        foreach ($this->getDomainDirectories('Observers') as $directory) {
            if (! is_dir($directory)) {
                continue;
            }

            foreach (File::allFiles($directory) as $file) {
                $observerClass = $this->getClassFromFile($file->getPathname());

                if (! $observerClass || ! class_exists($observerClass)) {
                    continue;
                }

                $modelClass = $this->getObservedModel($observerClass);

                if ($modelClass && class_exists($modelClass)) {
                    $modelClass::observe($observerClass);
                }
            }
        }
    }

    /**
     * Get all domain directories for a given subdirectory type.
     *
     * @return array<int, string>
     */
    protected function getDomainDirectories(string $subdirectory): array
    {
        $domainPath = base_path('src/Domain');
        $directories = [];

        if (! is_dir($domainPath)) {
            return $directories;
        }

        foreach (File::directories($domainPath) as $domain) {
            $path = $domain.'/'.$subdirectory;
            if (is_dir($path)) {
                $directories[] = $path;
            }
        }

        return $directories;
    }

    /**
     * Get the fully qualified class name from a file.
     */
    protected function getClassFromFile(string $filePath): ?string
    {
        $contents = file_get_contents($filePath);

        if (! $contents) {
            return null;
        }

        // Extract namespace
        if (! preg_match('/namespace\s+([^;]+);/', $contents, $namespaceMatch)) {
            return null;
        }

        // Extract class name
        if (! preg_match('/class\s+(\w+)/', $contents, $classMatch)) {
            return null;
        }

        return $namespaceMatch[1].'\\'.$classMatch[1];
    }

    /**
     * Determine the model class that an observer observes.
     *
     * Convention: UserObserver observes User model in the same domain.
     */
    protected function getObservedModel(string $observerClass): ?string
    {
        // Check if observer has a static $model property
        if (property_exists($observerClass, 'model')) {
            $reflection = new ReflectionClass($observerClass);
            $property = $reflection->getProperty('model');

            return $property->getDefaultValue();
        }

        // Convention: Domain\User\Observers\UserObserver -> Domain\User\Models\User
        if (preg_match('/^(.+)\\\\Observers\\\\(.+)Observer$/', $observerClass, $matches)) {
            $domainNamespace = $matches[1];
            $modelName = $matches[2];

            return $domainNamespace.'\\Models\\'.$modelName;
        }

        return null;
    }
}
