<?php

declare(strict_types=1);

namespace System\Filament\Concerns;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Provides comprehensive import options including:
 * - Match field selection (which field to use for finding existing records)
 * - Create/Update behavior toggles
 * - Validate-only mode
 */
trait HasImportOptions
{
    /**
     * Define the available match fields for this importer.
     * Each key is the field identifier, value is the display label.
     *
     * Example: ['id' => 'ID', 'code' => 'Code', 'email' => 'Email']
     *
     * @return array<string, string>
     */
    abstract public static function getMatchFields(): array;

    /**
     * Get the default match field.
     * Override to change the default selection.
     */
    public static function getDefaultMatchField(): string
    {
        $fields = static::getMatchFields();

        // Default to first non-ID field if available, otherwise ID
        foreach (array_keys($fields) as $field) {
            if ($field !== 'id') {
                return $field;
            }
        }

        return 'id';
    }

    /**
     * Find an existing record by the selected match field.
     * Returns null if no match found (will create new record).
     */
    abstract protected function findRecordByMatchField(string $matchField, mixed $value): ?Model;

    /**
     * @return array<\Filament\Forms\Components\Component>
     */
    public static function getOptionsFormComponents(): array
    {
        $matchFields = static::getMatchFields();

        return [
            Select::make('matchField')
                ->label('Match existing records by')
                ->options($matchFields)
                ->default(static::getDefaultMatchField())
                ->helperText('Field used to identify existing records for updates')
                ->required(),
            Checkbox::make('updateExisting')
                ->label('Update existing records')
                ->default(true)
                ->helperText('Update records that match the selected field'),
            Checkbox::make('createNew')
                ->label('Create new records')
                ->default(true)
                ->helperText('Create records that don\'t match any existing record'),
            Toggle::make('validateOnly')
                ->label('Validate only')
                ->helperText('Run validation without importing any data'),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function __invoke(array $data): void
    {
        if ($this->options['validateOnly'] ?? false) {
            DB::beginTransaction();

            try {
                parent::__invoke($data);
            } finally {
                DB::rollBack();
            }

            return;
        }

        parent::__invoke($data);
    }

    public function resolveRecord(): ?Model
    {
        $matchField = $this->options['matchField'] ?? static::getDefaultMatchField();
        $updateExisting = $this->options['updateExisting'] ?? true;
        $createNew = $this->options['createNew'] ?? true;

        // Get the value for the match field from imported data
        $value = $this->getMatchFieldValue($matchField);

        if (blank($value)) {
            // No match field value - create new if allowed
            return $createNew ? $this->makeNewRecord() : null;
        }

        // Try to find existing record
        $existingRecord = $this->findRecordByMatchField($matchField, $value);

        if ($existingRecord) {
            // Found existing - update if allowed
            return $updateExisting ? $existingRecord : null;
        }

        // No existing record - create new if allowed
        return $createNew ? $this->makeNewRecord() : null;
    }

    /**
     * Get the value for a match field from the imported data.
     * Override for custom field value extraction.
     */
    protected function getMatchFieldValue(string $matchField): mixed
    {
        return $this->data[$matchField] ?? null;
    }

    /**
     * Create a new model instance.
     * Override if you need custom initialization.
     */
    protected function makeNewRecord(): Model
    {
        $modelClass = static::getModel();

        return new $modelClass;
    }
}
