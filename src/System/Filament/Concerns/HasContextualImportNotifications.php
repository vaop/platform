<?php

declare(strict_types=1);

namespace System\Filament\Concerns;

use Filament\Actions\Imports\Models\Import;

/**
 * Provides contextual notification titles and bodies for imports.
 *
 * Generates appropriate notification messages based on:
 * - Whether validate-only mode was used
 * - Whether the import fully succeeded, partially failed, or completely failed
 */
trait HasContextualImportNotifications
{
    /**
     * Returns a contextual plural noun for the imported entity.
     * Override in your importer to customize (e.g., "countries", "metro areas").
     */
    protected static function getImportedEntityName(): string
    {
        return 'rows';
    }

    public static function getCompletedNotificationTitle(Import $import): string
    {
        $validateOnly = $import->importer_data['options']['validateOnly'] ?? false;
        $failed = $import->getFailedRowsCount();

        if ($validateOnly) {
            return $failed > 0 ? 'Validation completed with errors' : 'Validation passed';
        }

        if ($failed === $import->total_rows) {
            return 'Import failed';
        }

        if ($failed > 0) {
            return 'Import partially completed';
        }

        return 'Import completed';
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $count = number_format($import->successful_rows);
        $failed = number_format($import->getFailedRowsCount());
        $entityName = static::getImportedEntityName();

        if ($import->importer_data['options']['validateOnly'] ?? false) {
            $message = "Validated {$count} rows successfully.";

            if ((int) $failed > 0) {
                $message .= " {$failed} rows would fail.";
            }

            $message .= ' No data was imported.';

            return $message;
        }

        $message = "Imported {$count} {$entityName}.";

        if ((int) $failed > 0) {
            $message .= " {$failed} rows failed.";
        }

        return $message;
    }
}
