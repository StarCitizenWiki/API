<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;

abstract class AbstractShowSeoData
{
    /**
     * @param  array<int, array{label: string, url: string}>  $breadcrumbs
     * @return array<string, mixed>|null
     */
    protected function buildBreadcrumbStructuredData(array $breadcrumbs): ?array
    {
        $items = [];

        foreach ($breadcrumbs as $breadcrumb) {
            $name = $this->normalizeString($breadcrumb['label'] ?? null);
            $url = $this->normalizeString($breadcrumb['url'] ?? null);

            if ($name === null || $url === null) {
                continue;
            }

            $items[] = [
                '@type' => 'ListItem',
                'position' => count($items) + 1,
                'name' => $name,
                'item' => $url,
            ];
        }

        if ($items === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    /**
     * @param  array<string, mixed>  $properties
     * @return array<int, array<string, mixed>>
     */
    protected function buildPropertyValues(array $properties): array
    {
        $values = [];

        foreach ($properties as $name => $value) {
            $normalizedValue = $this->normalizeScalar($value);

            if ($normalizedValue === null) {
                continue;
            }

            $values[] = [
                '@type' => 'PropertyValue',
                'name' => $name,
                'value' => $normalizedValue,
            ];
        }

        return $values;
    }

    protected function resolveDescription(mixed $description): ?string
    {
        if (is_string($description)) {
            return $this->normalizeString($description);
        }

        if (! is_array($description)) {
            return null;
        }

        foreach (['en_EN', 'en'] as $preferredLocale) {
            $preferredValue = $this->normalizeString($description[$preferredLocale] ?? null);

            if ($preferredValue !== null) {
                return $preferredValue;
            }
        }

        foreach ($description as $value) {
            $resolved = $this->normalizeString($value);

            if ($resolved !== null) {
                return $resolved;
            }
        }

        return null;
    }

    protected function resolveVersionCode(Request $request): ?string
    {
        $queryVersion = $this->normalizeString($request->query('version'));

        if ($queryVersion !== null) {
            return $queryVersion;
        }

        if (! $request->hasSession()) {
            return null;
        }

        return $this->normalizeString($request->session()->get('game_version_code'));
    }

    abstract protected function fallbackShowUrl(?string $uuid, ?string $version): string;

    protected function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim(html_entity_decode($value));

        return $value === '' ? null : $value;
    }

    protected function normalizeScalar(mixed $value): string|int|float|null
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        return $this->normalizeString($value);
    }

    protected function normalizeInt(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    /**
     * @param  array<int, mixed>  $values
     * @return array<int, mixed>
     */
    protected function compactValues(array $values): array
    {
        return array_values(array_filter($values, static fn (mixed $value): bool => $value !== null && $value !== ''));
    }

    /**
     * @param  array<int, mixed>  $segments
     */
    protected function joinSegments(array $segments, string $glue = ' '): string
    {
        return implode($glue, $this->compactValues($segments));
    }
}
