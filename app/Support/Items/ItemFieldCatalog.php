<?php

declare(strict_types=1);

namespace App\Support\Items;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Throwable;

final class ItemFieldCatalog
{
    /**
     * Root field names that belong to the "Core" group.
     *
     * @var array<int, string>
     */
    private const CORE_FIELDS = [
        'uuid',
        'slug',
        'name',
        'class_name',
        'classification',
        'classification_label',
        'description',
        'size',
        'mass',
        'rarity',
        'grade',
        'class',
        'type',
        'type_label',
        'type_web_url',
        'sub_type',
        'sub_type_label',
        'web_url',
        'link',
        'updated_at',
        'version',
    ];

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
     * @return array<int, array<string, mixed>>
     */
    public function forTableBuilder(): array
    {
        $sorts = config('sorts.items', []);
        $coreSorts = $this->coreSortFields();
        $filters = $this->filterFields();

        return collect($this->all())
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
            ->sortBy([
                ['group', 'asc'],
                ['field', 'asc'],
            ])
            ->values()
            ->all();
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
        $root = str($field)->before('.')->toString();

        return match (true) {
            in_array($root, self::CORE_FIELDS, true) => 'Core',
            'manufacturer', 'manufacturer_description' => 'Manufacturer',
            'is_base_variant', 'is_craftable', 'base_variant', 'variants', 'related_items' => 'Variants & Crafting',
            'tags', 'required_tags', 'entity_tags', 'entity_tag_map', 'interactions' => 'Tags & Interactions',
            default => str($root)->replace('_', ' ')->headline()->toString(),
        };
    }
}
