<?php

declare(strict_types=1);

namespace App\Support\Items;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Throwable;

final class ItemFieldCatalog
{
    /**
     * @var array<int, array<string, mixed>>|null
     */
    private ?array $fields = null;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function all(): array
    {
        if ($this->fields !== null) {
            return $this->fields;
        }

        $path = storage_path('app/generated/item-fields.json');

        if (! File::exists($path)) {
            $this->buildMissingCatalog($path);
        }

        if (! File::exists($path)) {
            return $this->fields = [];
        }

        $decoded = json_decode(File::get($path), true);

        if (! is_array($decoded)) {
            return $this->fields = [];
        }

        return $this->fields = array_values(array_filter(
            $decoded,
            static fn (mixed $field): bool => is_array($field) && is_string($field['field'] ?? null),
        ));
    }

    private function buildMissingCatalog(string $path): void
    {
        if (! File::exists(base_path('swagger.yaml'))) {
            return;
        }

        try {
            Artisan::call('items:build-field-catalog', [
                '--output' => $path,
            ]);
        } catch (Throwable) {
            // The table can still render with configured columns if catalog generation fails.
        }
    }

    /**
     * Catalog payload intended for the Tabulator column builder.
     *
     * When a pre-resolved ItemTableConfig columns tree is supplied, its leaf
     * columns are merged in so the dialog shows every column the table can
     * render, with proper nested group titles. OpenAPI catalog entries that
     * aren't already represented by a config column are appended for the user
     * to opt into.
     *
     * @param  array<int, array<string, mixed>>|null  $tableColumns  Pre-resolved enriched columns tree (e.g. from ItemTableConfig::build()).
     * @return array<int, array<string, mixed>>
     */
    public function forTableBuilder(?array $tableColumns = null): array
    {
        $sorts = config('sorts.items', []);
        $coreSorts = $this->coreSortFields();
        $filters = $this->filterFields();

        $configEntries = $tableColumns === null
            ? []
            : $this->configColumnsToCatalog($tableColumns);

        $configFieldNames = array_column($configEntries, 'field');

        $catalogEntries = collect($this->all())
            ->filter(static fn (array $field): bool => ! in_array($field['field'] ?? null, $configFieldNames, true))
            ->map(static function (array $field) use ($sorts, $coreSorts, $filters): array {
                $fieldName = (string) $field['field'];
                $sortConfig = $sorts[$fieldName] ?? null;
                $filterConfig = $filters[$fieldName] ?? null;

                $field['group'] = self::fieldGroup($fieldName);
                $field['shortTitle'] = self::shortTitle(
                    (string) ($field['title'] ?? $fieldName),
                    $field['group'],
                );
                $field['sortable'] = is_array($sortConfig) || in_array($fieldName, $coreSorts, true);
                $field['sortField'] = is_array($sortConfig)
                    ? Arr::get($sortConfig, 'path')
                    : $fieldName;
                $field['filterable'] = is_array($filterConfig);

                if (is_array($filterConfig)) {
                    $field['filterField'] = $filterConfig['field'] ?? $fieldName;
                    $field['filterType'] = $filterConfig['type'] ?? 'input';
                }

                return $field;
            })
            ->all();

        $entries = [...$configEntries, ...$catalogEntries];

        return collect($entries)
            ->sortBy([
                fn (array $a, array $b) => match (true) {
                    $a['group'] === 'Core' => -1,
                    $b['group'] === 'Core' => 1,
                    $a['group'] === 'Manufacturer' => -1,
                    $b['group'] === 'Manufacturer' => 1,
                    default => 0,
                },
                ['group', 'asc'],
                ['field', 'asc'],
            ])
            ->values()
            ->all();
    }

    /**
     * Walk a resolved ItemTableConfig columns tree and emit one catalog entry
     * per leaf column, with group = joined ancestor titles and the column's
     * own metadata (sortable, filterable, formatter) preserved.
     *
     * @param  array<int, array<string, mixed>>  $columns
     * @return array<int, array<string, mixed>>
     */
    private function configColumnsToCatalog(array $columns, array $groupTitles = []): array
    {
        $entries = [];

        foreach ($columns as $column) {
            $title = is_string($column['title'] ?? null) ? (string) $column['title'] : null;
            $childGroups = $title !== null ? [...$groupTitles, $title] : $groupTitles;

            if (is_array($column['columns'] ?? null) && $column['columns'] !== []) {
                $entries = [...$entries, ...$this->configColumnsToCatalog($column['columns'], $childGroups)];

                continue;
            }

            $field = $column['field'] ?? null;
            if (! is_string($field) || $field === '') {
                continue;
            }

            $headerSort = $column['headerSort'] ?? null;
            $sortField = $column['sortField'] ?? Arr::get($column, 'sort.path') ?? $field;
            $headerFilter = $column['headerFilter'] ?? null;
            $group = count($childGroups) > 1
                ? $childGroups[0]
                : self::fieldGroup($field);

            $entry = [
                'field' => $field,
                'title' => $title ?? $field,
                'type' => 'string',
                'types' => [],
                'description' => null,
                'nullable' => true,
                'array' => false,
                'columnable' => true,
                'schema' => $childGroups === [] ? null : end($childGroups),
                'schemas' => $childGroups === [] ? [] : [end($childGroups)],
                'deprecated' => false,
                'formatter_params' => $column['formatterParams'] ?? null,
                'group' => $group,
                'shortTitle' => $title ?? $field,
                'sortable' => $headerSort === true || (is_string($sortField) && $sortField !== $field),
                'sortField' => $sortField,
                'filterable' => $headerFilter !== null && $headerFilter !== false,
            ];

            if ($headerFilter === 'list') {
                $entry['filterType'] = 'list';
            } elseif (is_string($headerFilter) && $headerFilter !== '') {
                $entry['filterType'] = 'input';
            }

            if (isset($column['formatter']) && (is_string($column['formatter']) || is_callable($column['formatter']))) {
                $entry['formatter'] = $column['formatter'];
            }

            if (isset($column['suffix']) && is_string($column['suffix'])) {
                $entry['suffix'] = $column['suffix'];
            }

            $entries[] = $entry;
        }

        return $entries;
    }

    /**
     * @return array<int, string>
     */
    private function coreSortFields(): array
    {
        return [
            'name',
            'class_name',
            'class',
            'size',
            'grade',
            'type',
            'sub_type',
            'classification',
            'manufacturer.name',
            'rarity',
        ];
    }

    /**
     * @return array<string, array{field?: string, type: string}>
     */
    private function filterFields(): array
    {
        return [
            'type' => ['type' => 'list'],
            'sub_type' => ['type' => 'list'],
            'manufacturer.name' => ['field' => 'manufacturer.name', 'type' => 'list'],
            'class_name' => ['type' => 'input'],
            'name' => ['type' => 'input'],
            'classification' => ['type' => 'input'],
            'size' => ['type' => 'list'],
            'grade' => ['type' => 'list'],
            'class' => ['type' => 'list'],
            'rarity' => ['type' => 'list'],
            'event_source' => ['type' => 'list'],
        ];
    }

    private static function shortTitle(string $title, string $group): string
    {
        $prefix = $group.' ';

        if (str_starts_with($title, $prefix)) {
            return trim(substr($title, strlen($prefix))) ?: $title;
        }

        return $title;
    }

    private static function fieldGroup(string $field): string
    {
        $segments = explode('.', $field);
        $root = $segments[0];

        if (in_array($root, self::coreRoots(), true)) {
            return 'Core';
        }

        if ($root === 'manufacturer' || $root === 'manufacturer_description') {
            return 'Manufacturer';
        }

        if (in_array($root, self::variantRoots(), true)) {
            return 'Variants & Crafting';
        }

        if (in_array($root, self::tagRoots(), true)) {
            return 'Tags & Interactions';
        }

        if (count($segments) <= 2) {
            return Str::headline($root);
        }

        return Str::headline($root).' / '.Str::headline($segments[1]);
    }

    /**
     * @return array<int, string>
     */
    private static function coreRoots(): array
    {
        return [
            'uuid', 'slug', 'name', 'class_name', 'classification', 'classification_label',
            'description', 'size', 'mass', 'rarity', 'event_source', 'grade', 'class',
            'type', 'type_label', 'type_web_url', 'sub_type', 'sub_type_label',
            'web_url', 'link', 'updated_at', 'version',
        ];
    }

    /**
     * @return array<int, string>
     */
    private static function variantRoots(): array
    {
        return ['is_base_variant', 'is_craftable', 'base_variant', 'variants', 'related_items'];
    }

    /**
     * @return array<int, string>
     */
    private static function tagRoots(): array
    {
        return ['tags', 'required_tags', 'entity_tags', 'entity_tag_map', 'interactions'];
    }
}
