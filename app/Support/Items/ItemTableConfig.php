<?php

declare(strict_types=1);

namespace App\Support\Items;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

final class ItemTableConfig
{
    /**
     * Cached sorts configuration.
     *
     * @var array<string, array{path: string, cast: string}>|null
     */
    private ?array $sortsConfig = null;

    /**
     * @return array{title:string,columns:array<int,array<string,mixed>>,headerFilterOptionsMap:array<string,string>}
     */
    public function build(?string $type): array
    {
        $resolvedType = $this->normalizeType($type);
        $columns = $this->columnsForType($resolvedType);
        $headerFilterOptionsMap = $this->headerFilterOptionsMapForType($resolvedType);

        return [
            'title' => $this->titleForType($resolvedType),
            'columns' => $columns,
            'headerFilterOptionsMap' => $headerFilterOptionsMap,
        ];
    }

    private function columnsForType(?string $type): array
    {
        $columns = config('items.table.columns', []);
        $overrides = $this->overridesForType($type);

        if ($overrides !== []) {
            $removeFields = Arr::get($overrides, 'remove_fields', []);
            if (is_array($removeFields) && $removeFields !== []) {
                $columns = $this->removeColumns($columns, $removeFields);
            }

            $sharedGroups = $this->resolveSharedGroups($overrides);
            $sharedInsertAt = Arr::get($overrides, 'shared_insert_at');

            $additionalColumns = Arr::get($overrides, 'add_columns', []);
            $addColumnsInsertAt = Arr::get($overrides, 'add_columns_insert_at');

            if ($sharedGroups !== [] && is_array($additionalColumns) && $additionalColumns !== []) {
                if ($this->isPositiveInsertAt($addColumnsInsertAt) && $this->isPositiveInsertAt($sharedInsertAt)) {
                    if ($addColumnsInsertAt < $sharedInsertAt) {
                        $columns = $this->insertColumnsAt($columns, $additionalColumns, $addColumnsInsertAt, true);
                        $columns = $this->insertColumnsAt($columns, $sharedGroups, $sharedInsertAt, true);
                    } else {
                        $columns = $this->insertColumnsAt($columns, $sharedGroups, $sharedInsertAt, true);
                        $columns = $this->insertColumnsAt($columns, $additionalColumns, $addColumnsInsertAt, true);
                    }
                } else {
                    $columns = $this->insertColumnsAt($columns, $sharedGroups, $sharedInsertAt, true);
                    $columns = $this->insertColumnsAt($columns, $additionalColumns, $addColumnsInsertAt, true);
                }
            } else {
                if ($sharedGroups !== []) {
                    $columns = $this->insertColumnsAt($columns, $sharedGroups, $sharedInsertAt, true);
                }

                if (is_array($additionalColumns) && $additionalColumns !== []) {
                    $columns = $this->insertColumnsAt($columns, $additionalColumns, $addColumnsInsertAt, true);
                }
            }
        }

        if ($type !== null) {
            $columns = $this->removeColumns($columns, ['type']);
        }

        // Enrich all columns with sortField from sorts configuration
        $sortsConfig = $this->getSortsConfig();
        $columns = array_map(
            fn (array $column): array => $this->enrichColumn($column, $sortsConfig),
            $columns
        );

        return $columns;
    }

    private function headerFilterOptionsMapForType(?string $type): array
    {
        $headerFilterOptionsMap = config('items.table.header_filter_options_map', []);
        $overrides = $this->overridesForType($type);

        $overrideMap = Arr::get($overrides, 'header_filter_options_map', []);
        if (is_array($overrideMap) && $overrideMap !== []) {
            $headerFilterOptionsMap = array_merge($headerFilterOptionsMap, $overrideMap);
        }

        if ($type !== null) {
            $headerFilterOptionsMap = Arr::except($headerFilterOptionsMap, ['type']);
        }

        return $headerFilterOptionsMap;
    }

    private function titleForType(?string $type): string
    {
        if ($type === null) {
            return (string) config('items.title.default', 'Items');
        }

        $overrideTitle = Arr::get($this->overridesForType($type), 'title');
        if (is_string($overrideTitle) && $overrideTitle !== '') {
            return $overrideTitle;
        }

        $format = (string) config('items.title.format', '%s Items');

        return sprintf($format, Str::headline($type));
    }

    private function overridesForType(?string $type): array
    {
        if ($type === null) {
            return [];
        }

        $overrides = config('items.type_overrides', []);
        $typeKey = Str::lower($type);

        return $overrides[$type] ?? $overrides[$typeKey] ?? [];
    }

    /**
     * @param  array<int, array<string, mixed>>  $columns
     * @param  array<int, string>  $fields
     * @return array<int, array<string, mixed>>
     */
    private function removeColumns(array $columns, array $fields): array
    {
        if ($fields === []) {
            return $columns;
        }

        return array_values(array_filter($columns, function (array $column) use ($fields): bool {
            $field = $column['field'] ?? null;

            return ! in_array($field, $fields, true);
        }));
    }

    private function normalizeType(?string $type): ?string
    {
        if ($type === null) {
            return null;
        }

        $trimmedType = trim($type);

        return $trimmedType === '' ? null : $trimmedType;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<int, array<string, mixed>>
     */
    private function resolveSharedGroups(array $overrides): array
    {
        $sharedKeys = Arr::get($overrides, 'shared', []);
        if (! is_array($sharedKeys) || $sharedKeys === []) {
            return [];
        }

        $sharedGroups = config('items.shared_groups', []);
        $sharedOverrides = Arr::get($overrides, 'shared_overrides', []);
        $resolvedGroups = [];

        foreach ($sharedKeys as $key) {
            if (! isset($sharedGroups[$key])) {
                continue;
            }

            $group = $sharedGroups[$key];

            // Apply overrides if provided
            if (isset($sharedOverrides[$key]) && is_array($sharedOverrides[$key])) {
                $group = array_replace_recursive($group, $sharedOverrides[$key]);
            }

            $resolvedGroups[] = $group;
        }

        return $resolvedGroups;
    }

    /**
     * Find the index of the view button column
     *
     * @param  array<int, array<string, mixed>>  $columns
     * @return int|null Index of view button, or null if not found
     */
    private function findViewButtonIndex(array $columns): ?int
    {
        return array_find_key($columns, fn ($column) => ($column['field'] ?? null) === 'uuid');
    }

    /**
     * Insert columns at a specific position with view button protection
     *
     * @param  array<int, array<string, mixed>>  $columns  Base columns
     * @param  array<int, array<string, mixed>>  $newColumns  Columns to insert
     * @param  int|null  $insertAt  Position to insert (null = append)
     * @param  bool  $protectViewButton  Whether to ensure view button stays last
     * @return array<int, array<string, mixed>>
     */
    private function insertColumnsAt(
        array $columns,
        array $newColumns,
        ?int $insertAt,
        bool $protectViewButton = true
    ): array {
        if ($newColumns === []) {
            return $columns;
        }

        // If no position specified, append (backwards compatible)
        if ($insertAt === null) {
            return array_values(array_merge($columns, $newColumns));
        }

        if ($this->isPositiveInsertAt($insertAt)) {
            $insertAt = $this->findViewButtonIndex($columns) ?? count($columns);
        }

        $columnCount = count($columns);

        // Handle negative indices (count from end)
        if ($insertAt < 0) {
            $insertAt = max(0, $columnCount + $insertAt);
        }

        // Clamp to valid range
        $insertAt = max(0, min($insertAt, $columnCount));

        // Protect view button if requested
        if ($protectViewButton) {
            $viewButtonIndex = $this->findViewButtonIndex($columns);
            if ($viewButtonIndex !== null && $insertAt > $viewButtonIndex) {
                $insertAt = $viewButtonIndex;
            }
        }

        // Insert columns
        return array_values(array_merge(
            array_slice($columns, 0, $insertAt),
            $newColumns,
            array_slice($columns, $insertAt)
        ));
    }

    private function isPositiveInsertAt(?int $insertAt): bool
    {
        return $insertAt !== null && $insertAt > 0;
    }

    /**
     * Get sorts configuration, cached for the request lifecycle.
     *
     * @return array<string, array{path: string, cast: string}>
     */
    private function getSortsConfig(): array
    {
        if ($this->sortsConfig === null) {
            $this->sortsConfig = config('sorts.items', []);
        }

        return $this->sortsConfig;
    }

    /**
     * Enrich a single column with sortField from sorts configuration.
     *
     * Matches column 'field' to sort config key and adds 'sortField' from 'path'.
     * Example: field='mass' → sortField='Mass' (from sorts config)
     *
     * @param  array<string, mixed>  $column  Column definition
     * @param  array<string, array{path: string, cast: string}>  $sortsConfig  Sorts configuration
     * @return array<string, mixed> Enriched column
     */
    private function enrichColumnSort(array $column, array $sortsConfig): array
    {
        // Skip if no field defined
        if (! isset($column['field']) || ! is_string($column['field'])) {
            return $column;
        }

        $field = $column['field'];

        // Skip if field already has sortField (manual override)
        if (isset($column['sortField'])) {
            return $column;
        }

        // Lookup sort configuration by field name
        if (! isset($sortsConfig[$field])) {
            // Not all fields are sortable - this is normal
            return $column;
        }

        $sortConfig = $sortsConfig[$field];

        // Add sortField from path
        $column['sortField'] = $sortConfig['path'];

        // Optionally add complete sort metadata
        $column['sort'] = [
            'path' => $sortConfig['path'],
            'cast' => $sortConfig['cast'] ?? 'text',
        ];

        return $column;
    }

    /**
     * Recursively enrich a column and its nested columns with sortField.
     *
     * @param  array<string, mixed>  $column  Column definition (may contain nested columns)
     * @param  array<string, array{path: string, cast: string}>  $sortsConfig  Sorts configuration
     * @return array<string, mixed> Enriched column
     */
    private function enrichColumn(array $column, array $sortsConfig): array
    {
        // Handle nested column groups
        if (isset($column['columns']) && is_array($column['columns'])) {
            $column['columns'] = array_map(
                fn (array $nestedColumn): array => $this->enrichColumn($nestedColumn, $sortsConfig),
                $column['columns']
            );
        }

        // Enrich this column's sortField
        return $this->enrichColumnSort($column, $sortsConfig);
    }
}
