<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class BuildItemFieldCatalog extends Command
{
    private const array COMPOSITION_KEYS = ['allOf', 'anyOf', 'oneOf'];

    private const array SCALAR_FIELD_TYPES = ['boolean', 'integer', 'number', 'string'];

    private const array COLUMNABLE_ARRAY_ITEM_TYPES = ['integer', 'number', 'string'];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'items:build-field-catalog
        {--input= : OpenAPI YAML file to read}
        {--output= : JSON file to write}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Build a generated item field catalog from the OpenAPI game_item schema';

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $schemas = [];

    /**
     * @var array<string, array<string, mixed>>
     */
    private array $fields = [];

    /**
     * Path prefixes that originate from a deprecated schema. Fields emitted
     * under any of these prefixes are marked deprecated.
     *
     * @var array<string, true>
     */
    private array $deprecatedPaths = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $inputPath = (string) ($this->option('input') ?: base_path('swagger.yaml'));
        $outputPath = (string) ($this->option('output') ?: storage_path('app/generated/item-fields.json'));

        if (! File::exists($inputPath)) {
            $this->error(sprintf('OpenAPI file not found: %s', $inputPath));

            return self::FAILURE;
        }

        try {
            $spec = Yaml::parseFile($inputPath);
        } catch (ParseException $exception) {
            $this->error(sprintf('Unable to parse OpenAPI YAML: %s', $exception->getMessage()));

            return self::FAILURE;
        }

        if (! is_array($spec)) {
            $this->error('OpenAPI file did not parse to an object.');

            return self::FAILURE;
        }

        $schemas = data_get($spec, 'components.schemas');
        if (! is_array($schemas)) {
            $this->error('OpenAPI components.schemas not found.');

            return self::FAILURE;
        }

        $gameItemSchema = $schemas['game_item'] ?? null;
        if (! is_array($gameItemSchema)) {
            $this->error('OpenAPI schema components.schemas.game_item not found.');

            return self::FAILURE;
        }

        $this->schemas = $schemas;
        $this->fields = [];
        $this->deprecatedPaths = [];

        $this->collectFields(
            schema: $gameItemSchema,
            path: '',
            sourceSchema: 'game_item',
            nullable: (bool) ($gameItemSchema['nullable'] ?? false),
            deprecated: (bool) ($gameItemSchema['deprecated'] ?? false),
        );

        ksort($this->fields, SORT_STRING);

        $this->fields = array_filter($this->fields, static fn (array $field): bool => ! ($field['deprecated'] ?? false));

        $fields = array_values(array_map(static function (array $field): array {
            $field['types'] = array_values($field['types']);
            $field['schemas'] = array_values($field['schemas']);

            return $field;
        }, $this->fields));

        File::ensureDirectoryExists(dirname($outputPath));
        File::put($outputPath, json_encode($fields, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL);

        $this->info(sprintf('Wrote %d item fields to %s.', count($fields), $outputPath));

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $schema
     * @param  array<int, string>  $refStack
     */
    private function collectFields(
        array $schema,
        string $path,
        string $sourceSchema,
        bool $nullable = false,
        bool $deprecated = false,
        array $refStack = [],
    ): void {
        $nullable = $nullable || ($schema['nullable'] ?? false);
        $deprecated = $deprecated || ($schema['deprecated'] ?? false) || $this->isPathDeprecated($path);

        $ref = $schema['$ref'] ?? null;
        if (is_string($ref)) {
            $refName = $this->localSchemaName($ref);
            if ($refName === null || in_array($refName, $refStack, true)) {
                return;
            }

            $resolved = $this->schemas[$refName] ?? null;
            if (! is_array($resolved)) {
                return;
            }

            // mark the path prefix as deprecated so leaves emitted through it inherit the flag,
            if ($resolved['deprecated'] ?? false) {
                if ($path !== '') {
                    $this->deprecatedPaths[$path] = true;
                }

                return;
            }

            $this->collectFields(
                schema: $resolved,
                path: $path,
                sourceSchema: $refName,
                nullable: $nullable,
                deprecated: $deprecated,
                refStack: [...$refStack, $refName],
            );

            return;
        }

        foreach (self::COMPOSITION_KEYS as $compositionKey) {
            $branches = $schema[$compositionKey] ?? null;
            if (! is_array($branches)) {
                continue;
            }

            foreach ($branches as $branch) {
                if (! is_array($branch)) {
                    continue;
                }

                $this->collectFields(
                    schema: $branch,
                    path: $path,
                    sourceSchema: $sourceSchema,
                    nullable: $nullable,
                    deprecated: $deprecated,
                    refStack: $refStack,
                );
            }
        }

        if (($schema['type'] ?? null) === 'array' || array_key_exists('items', $schema)) {
            if ($path !== '') {
                $itemTypes = $this->schemaTypes($schema['items'] ?? []);
                $this->addField(
                    field: $path,
                    type: $this->singleOrMixedType($itemTypes),
                    sourceSchema: $sourceSchema,
                    description: $this->schemaDescription($schema),
                    nullable: $nullable,
                    array: true,
                    deprecated: $deprecated,
                    columnable: $this->hasOnlyColumnableArrayItemTypes($itemTypes),
                    suffix: $this->schemaSuffix($schema),
                    formatter: $this->schemaFormatter($schema),
                    formatterParams: $this->schemaFormatterParams($schema),
                );

                // For translation-object arrays (e.g. `description: { en_EN: ..., de_DE: ... }`),
                // emit a synthetic locale-suffixed sibling so the column builder can offer the
                // current-language text as a selectable column.
                if ($this->isTranslationSchema($schema['items'] ?? [])) {
                    $localeField = $path.'.en_EN';
                    $this->addField(
                        field: $localeField,
                        type: 'string',
                        sourceSchema: $sourceSchema,
                        description: 'English (en_EN) entry from the translation object.',
                        nullable: true,
                        array: false,
                        deprecated: $deprecated,
                        columnable: true,
                    );
                    $this->fields[$localeField]['title'] = ucfirst($path).' (en_EN)';
                }
            }

            // Arrays of objects are intentionally not expanded into indexed dot paths.
            return;
        }

        $properties = $schema['properties'] ?? null;
        if (is_array($properties)) {
            foreach ($properties as $propertyName => $propertySchema) {
                if (! is_string($propertyName) || ! is_array($propertySchema)) {
                    continue;
                }

                $this->collectFields(
                    schema: $propertySchema,
                    path: $path === '' ? $propertyName : $path.'.'.$propertyName,
                    sourceSchema: $sourceSchema,
                    nullable: $nullable,
                    deprecated: $deprecated,
                    refStack: $refStack,
                );
            }

            return;
        }

        $type = $this->schemaType($schema);
        if ($path !== '' && in_array($type, self::SCALAR_FIELD_TYPES, true)) {
            $this->addField(
                field: $path,
                type: $type,
                sourceSchema: $sourceSchema,
                description: $this->schemaDescription($schema),
                nullable: $nullable,
                array: false,
                deprecated: $deprecated,
                columnable: true,
                suffix: $this->schemaSuffix($schema),
                formatter: $this->schemaFormatter($schema),
                formatterParams: $this->schemaFormatterParams($schema),
            );
        }
    }

    private function localSchemaName(string $ref): ?string
    {
        $prefix = '#/components/schemas/';
        if (! str_starts_with($ref, $prefix)) {
            return null;
        }

        return urldecode(substr($ref, strlen($prefix)));
    }

    private function isPathDeprecated(string $path): bool
    {
        if ($path === '') {
            return false;
        }

        $segment = $path;
        while (true) {
            if (isset($this->deprecatedPaths[$segment])) {
                return true;
            }

            $pos = strrpos($segment, '.');
            if ($pos === false) {
                return false;
            }

            $segment = substr($segment, 0, $pos);
        }
    }

    /**
     * @param  array<string, mixed>|mixed  $schema
     * @return array<int, string>
     */
    private function schemaTypes(mixed $schema, array $refStack = []): array
    {
        if (! is_array($schema)) {
            return ['mixed'];
        }

        $ref = $schema['$ref'] ?? null;
        if (is_string($ref)) {
            $refName = $this->localSchemaName($ref);
            if ($refName === null || in_array($refName, $refStack, true)) {
                return ['mixed'];
            }

            $resolved = $this->schemas[$refName] ?? null;

            return $this->schemaTypes($resolved, [...$refStack, $refName]);
        }

        $types = [];
        foreach (self::COMPOSITION_KEYS as $compositionKey) {
            $branches = $schema[$compositionKey] ?? null;
            if (! is_array($branches)) {
                continue;
            }

            foreach ($branches as $branch) {
                $types = [...$types, ...$this->schemaTypes($branch, $refStack)];
            }
        }

        $type = $this->schemaType($schema);
        if ($type !== 'mixed') {
            $types[] = $type;
        }

        if ($types === []) {
            return ['mixed'];
        }

        return $this->sortedUnique($types);
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    private function schemaType(array $schema): string
    {
        $type = $schema['type'] ?? null;

        if (is_array($type)) {
            $type = array_values(array_filter($type, static fn (mixed $value): bool => $value !== 'null'));

            return count($type) === 1 && is_string($type[0]) ? $type[0] : 'mixed';
        }

        if (is_string($type) && $type !== '') {
            return $type;
        }

        if (isset($schema['properties']) && is_array($schema['properties'])) {
            return 'object';
        }

        return 'mixed';
    }

    private function singleOrMixedType(array $types): string
    {
        $types = $this->sortedUnique(array_filter($types, static fn (string $type): bool => $type !== 'mixed'));

        return count($types) === 1 ? $types[0] : 'mixed';
    }

    /**
     * @param  array<int, string>  $types
     */
    private function hasOnlyColumnableArrayItemTypes(array $types): bool
    {
        return $types !== [] && array_diff($types, self::COLUMNABLE_ARRAY_ITEM_TYPES) === [];
    }

    /**
     * Detect whether a schema describes a translation object (locale-keyed
     * properties like `en`, `de_DE`, `fr`, `zh_CN`). A schema matches when all
     * of its property names are either a 2-letter language code or a
     * `xx_YY` locale code. The schema may be passed directly or via `$ref`.
     */
    private function isTranslationSchema(array $schema): bool
    {
        $ref = $schema['$ref'] ?? null;
        if (is_string($ref)) {
            $name = $this->localSchemaName($ref);

            if ($name === null || ! isset($this->schemas[$name])) {
                return false;
            }

            $schema = $this->schemas[$name];
        }

        $properties = $schema['properties'] ?? null;

        if (! is_array($properties) || $properties === []) {
            return false;
        }

        return array_all(array_keys($properties), fn($name) => is_string($name) && preg_match('/^[a-z]{2}(_[A-Z]{2})?$/', $name) === 1);
    }

    /**
     * @param  array<int, string>  $values
     * @return array<int, string>
     */
    private function sortedUnique(array $values): array
    {
        $values = array_values(array_unique($values));
        sort($values);

        return $values;
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    private function schemaDescription(array $schema): ?string
    {
        $description = $schema['description'] ?? null;

        return is_string($description) && $description !== '' ? $description : null;
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    private function schemaSuffix(array $schema): ?string
    {
        $suffix = $schema['x-suffix'] ?? null;

        return is_string($suffix) && $suffix !== '' ? $suffix : null;
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    private function schemaFormatter(array $schema): ?string
    {
        $formatter = $schema['x-tabulator-formatter'] ?? null;

        return is_string($formatter) && $formatter !== '' ? $formatter : null;
    }

    /**
     * @param  array<string, mixed>  $schema
     */
    private function schemaFormatterParams(array $schema): ?array
    {
        $params = $schema['x-formatter-params'] ?? null;

        return is_array($params) && $params !== [] ? $params : null;
    }

    private function addField(
        string $field,
        string $type,
        string $sourceSchema,
        ?string $description,
        bool $nullable,
        bool $array,
        bool $deprecated,
        bool $columnable,
        ?string $suffix = null,
        ?string $formatter = null,
        ?array $formatterParams = null,
    ): void {
        $entry = $this->fields[$field] ?? [
            'field' => $field,
            'title' => Str::headline(str_replace('.', ' ', $field)),
            'type' => $type,
            'types' => [],
            'description' => null,
            'nullable' => false,
            'array' => false,
            'columnable' => true,
            'schema' => $sourceSchema,
            'schemas' => [],
            'deprecated' => false,
            'formatter_params' => null,
        ];

        $entry['types'][] = $type;
        $entry['types'] = $this->sortedUnique($entry['types']);
        $entry['type'] = count($entry['types']) === 1 ? $entry['types'][0] : 'mixed';

        if ($entry['description'] === null && $description !== null) {
            $entry['description'] = $description;
        }

        $entry['nullable'] = $entry['nullable'] || $nullable;
        $entry['array'] = $entry['array'] || $array;
        $entry['columnable'] = $entry['columnable'] && $columnable;
        $entry['deprecated'] = $entry['deprecated'] || $deprecated;

        if ($suffix !== null && ($entry['suffix'] ?? null) === null) {
            $entry['suffix'] = $suffix;
        }

        if ($formatter !== null && ($entry['formatter'] ?? null) === null) {
            $entry['formatter'] = $formatter;
        }

        if ($formatterParams !== null && ($entry['formatter_params'] ?? null) === null) {
            $entry['formatter_params'] = $formatterParams;
        }

        $entry['schemas'][] = $sourceSchema;
        $entry['schemas'] = $this->sortedUnique($entry['schemas']);
        $entry['schema'] = $entry['schemas'][0] ?? $sourceSchema;

        $this->fields[$field] = $entry;
    }
}
