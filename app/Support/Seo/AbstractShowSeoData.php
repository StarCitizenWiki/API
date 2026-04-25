<?php

declare(strict_types=1);

namespace App\Support\Seo;

abstract class AbstractShowSeoData extends AbstractSeoData
{
    abstract protected function showRouteName(): string;

    protected function showRouteParameterName(): string
    {
        return 'identifier';
    }

    protected function fallbackShowUrl(?string $identifier, ?string $version): string
    {
        if ($identifier === null) {
            return url()->current();
        }

        return route($this->showRouteName(), array_filter([
            $this->showRouteParameterName() => $identifier,
            'version' => $version,
        ]));
    }

    protected function resolveDescription(mixed $description): ?string
    {
        if (is_string($description)) {
            $decoded = trim(html_entity_decode($description));

            return $decoded === '' ? null : $decoded;
        }

        if (! is_array($description)) {
            return null;
        }

        foreach (['en_EN', 'en'] as $preferredLocale) {
            $preferredValue = $description[$preferredLocale] ?? null;
            if (is_string($preferredValue)) {
                $decoded = trim(html_entity_decode($preferredValue));
                if ($decoded !== '') {
                    return $decoded;
                }
            }
        }

        foreach ($description as $value) {
            if (is_string($value)) {
                $decoded = trim(html_entity_decode($value));
                if ($decoded !== '') {
                    return $decoded;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<int, array{label: string, url: string}>  $breadcrumbs
     */
    protected function resolveLeafBreadcrumbLabel(array $breadcrumbs): ?string
    {
        if (count($breadcrumbs) < 2) {
            return null;
        }

        return $breadcrumbs[count($breadcrumbs) - 2]['label'] ?? null;
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<int, array<string, mixed>>
     */
    protected function buildPropertyValues(array $properties): array
    {
        $values = [];

        foreach ($properties as $name => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $values[] = [
                '@type' => 'PropertyValue',
                'name' => $name,
                'value' => $value,
            ];
        }

        return $values;
    }

    /**
     * @param  array<string, string|int|float|null>  $additionalProperties
     * @return array<string, mixed>
     */
    protected function buildBaseEntityStructuredData(
        string $schemaType,
        string $name,
        string $description,
        string $url,
        string $category,
        array $additionalProperties = [],
    ): array {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => $schemaType,
            'name' => $name,
            'description' => $description,
            'url' => $url,
            'category' => $category,
        ];

        $propertyValues = $this->buildPropertyValues($additionalProperties);

        if ($propertyValues !== []) {
            $schema['additionalProperty'] = $propertyValues;
        }

        return $schema;
    }
}
