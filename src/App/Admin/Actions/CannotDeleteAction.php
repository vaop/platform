<?php

declare(strict_types=1);

namespace App\Admin\Actions;

use Filament\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;

class CannotDeleteAction extends Action
{
    /**
     * @var array<string, array{label: string, pluralLabel: string, resource: class-string|null, filterKey: string|null}>
     */
    protected array $relationships = [];

    protected string $recordLabel = 'record';

    public static function getDefaultName(): ?string
    {
        return 'cannotDelete';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Cannot Delete')
            ->color('warning')
            ->icon('fas-triangle-exclamation')
            ->requiresConfirmation()
            ->modalIcon('fas-triangle-exclamation')
            ->modalIconColor('warning')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close');
    }

    public function recordLabel(string $label): static
    {
        $this->recordLabel = $label;

        $this->modalHeading("Cannot Delete {$label}");

        return $this;
    }

    /**
     * @param  class-string|null  $resource
     */
    public function checkRelationship(
        string $relationship,
        string $label,
        ?string $pluralLabel = null,
        ?string $resource = null,
        ?string $filterKey = null,
    ): static {
        $this->relationships[$relationship] = [
            'label' => $label,
            'pluralLabel' => $pluralLabel ?? $label.'s',
            'resource' => $resource,
            'filterKey' => $filterKey,
        ];

        $this->configureVisibilityAndDescription();

        return $this;
    }

    protected function configureVisibilityAndDescription(): void
    {
        $this->visible(fn (Model $record): bool => $this->hasAnyRelatedRecords($record));
        $this->modalDescription(fn (Model $record): HtmlString => $this->buildDescription($record));
    }

    protected function hasAnyRelatedRecords(Model $record): bool
    {
        foreach (array_keys($this->relationships) as $relationship) {
            if ($record->{$relationship}()->exists()) {
                return true;
            }
        }

        return false;
    }

    protected function buildDescription(Model $record): HtmlString
    {
        $lines = [];

        foreach ($this->relationships as $relationship => $config) {
            $count = $record->{$relationship}()->count();

            if ($count === 0) {
                continue;
            }

            $label = $count === 1 ? $config['label'] : $config['pluralLabel'];

            if ($config['resource'] !== null && $config['filterKey'] !== null) {
                $url = $config['resource']::getUrl('index', [
                    'filters' => [
                        $config['filterKey'] => ['value' => $record->getKey()],
                    ],
                ]);
                $lines[] = "<a href=\"{$url}\" class=\"text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 underline font-medium\" target=\"_blank\">{$count} {$label}</a>";
            } else {
                $lines[] = "{$count} {$label}";
            }
        }

        $listHtml = implode(', ', $lines);

        return new HtmlString(
            '<p>This '.strtolower($this->recordLabel)." has {$listHtml}<br />that must be reassigned or deleted first.</p>"
        );
    }
}
