<?php

declare(strict_types=1);

namespace System\Filament\Concerns;

/**
 * Processes small imports synchronously for immediate user feedback.
 *
 * Imports with 500 rows or fewer are processed synchronously,
 * while larger imports are queued for async processing.
 */
trait SynchronizesSmallImports
{
    protected int $syncRowThreshold = 500;

    public function getJobConnection(): ?string
    {
        if ($this->import->total_rows <= $this->syncRowThreshold) {
            return 'sync';
        }

        return parent::getJobConnection();
    }
}
