<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Support\Format;

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
        ?string $brand = null,
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

        if ($brand !== null) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name' => $brand,
            ];
        }

        return $schema;
    }

    protected function formatMass(float|int $mass): string
    {
        if ($mass >= 1000) {
            return Format::number($mass, 0).' kg';
        }

        return rtrim(rtrim(Format::number($mass, 2), '0'), '.').' kg';
    }

    protected function buildImage(array $entity): ?string
    {
        $images = data_get($entity, 'images');
        if (! is_array($images) || $images === []) {
            return null;
        }

        return data_get($images, '0.original_url')
            ?? data_get($images, '0.thumbnail_url');
    }

    /**
     * @param  array<int, array{price_buy?: int|float, price_sell?: int|float, terminal_name?: string}>  $uexPrices
     * @return array<string, mixed>|null
     */
    protected function buildUexAggregateOffer(array $uexPrices): ?array
    {
        $buyPrices = array_filter(array_map(
            static fn (array $price) => ($price['price_buy'] ?? 0) > 0 ? (float) $price['price_buy'] : null,
            $uexPrices,
        ));

        $aggregateOffer = [
            '@type' => 'AggregateOffer',
            'priceCurrency' => 'aUEC',
        ];

        if ($buyPrices !== []) {
            $aggregateOffer['lowPrice'] = min($buyPrices);
            $aggregateOffer['highPrice'] = max($buyPrices);
            $aggregateOffer['offerCount'] = count($buyPrices);
        }

        $individualOffers = [];
        $terminals = array_slice($uexPrices, 0, 5);
        foreach ($terminals as $price) {
            $buyPrice = ($price['price_buy'] ?? 0) > 0 ? (float) $price['price_buy'] : null;
            if ($buyPrice === null) {
                continue;
            }

            $offer = [
                '@type' => 'Offer',
                'price' => $buyPrice,
                'priceCurrency' => 'aUEC',
                'availability' => 'https://schema.org/InStock',
            ];

            $terminalName = $price['terminal_name'] ?? null;
            if ($terminalName !== null) {
                $offer['seller'] = [
                    '@type' => 'Organization',
                    'name' => $terminalName,
                ];
            }

            $individualOffers[] = $offer;
        }

        if ($individualOffers !== []) {
            $aggregateOffer['offers'] = $individualOffers;
        }

        return $individualOffers !== [] ? $aggregateOffer : null;
    }
}
