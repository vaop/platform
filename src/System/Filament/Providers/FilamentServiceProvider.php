<?php

declare(strict_types=1);

namespace System\Filament\Providers;

use Filament\Actions\Exports\Models\Export as BaseExport;
use Filament\Actions\Imports\Events\ImportStarted;
use Filament\Actions\Imports\Models\FailedImportRow as BaseFailedImportRow;
use Filament\Actions\Imports\Models\Import as BaseImport;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use System\Filament\Listeners\StoreImportOptions;
use System\Filament\Models\Export;
use System\Filament\Models\FailedImportRow;
use System\Filament\Models\Import;

class FilamentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(BaseImport::class, Import::class);
        $this->app->bind(BaseExport::class, Export::class);
        $this->app->bind(BaseFailedImportRow::class, FailedImportRow::class);
    }

    public function boot(): void
    {
        Event::listen(ImportStarted::class, StoreImportOptions::class);
    }
}
