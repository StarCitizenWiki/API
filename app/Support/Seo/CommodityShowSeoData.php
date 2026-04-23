<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class CommodityShowSeoData extends AbstractShowSeoData
{
    /**
     * @return array<string, mixed>
     */
    public function build(array $commodity, Request $request): array
    {
        $commodityName = $this->normalizeString(data_get($commodity, 'name')) ?? 'Commodity';
        $slug = $this->normalizeString(data_get($commodity, 'slug'));
        $uuid = $this->normalizeString(data_get($commodity, 'uuid'));
        $kind = $this->normalizeString(data_get($commodity, 'kind'));
        $tier = $this->normalizeScalar(data_get($commodity, 'tier'));
        $density = $this->normalizeScalar(data_get($commodity, 'density'));
        $instability = $this->normalizeScalar(data_get($commodity, 'instability'));
        $resistance = $this->normalizeScalar(data_get($commodity, 'resistance'));
        $description = $this->resolveDescription(data_get($commodity, 'description'));
        $version = $this->resolveVersionCode($request);
        $canonicalUrl = $this->normalizeString(data_get($commodity, 'web_url'))
            ?? $this->fallbackShowUrl($slug ?? $uuid, $version);
        $breadcrumbs = $this->buildBreadcrumbs($commodity, $commodityName, $canonicalUrl, $version);

        $metaTitle = $this->buildMetaTitle($commodityName, $kind, $tier);
        $metaDescription = Str::limit(
            $description ?? $this->buildFallbackDescription($commodityName, $kind, $tier),
            160,
        );

        $category = $this->resolveLeafBreadcrumbLabel($breadcrumbs) ?? $kind ?? 'Star Citizen Commodity';

        return [
            'title' => $metaTitle,
            'metaDescription' => $metaDescription,
            'canonicalUrl' => $canonicalUrl,
            'keywords' => $this->compactValues([
                $commodityName,
                $kind,
                $tier !== null ? 'Tier '.$tier : null,
                'Star Citizen',
                'SC',
            ]),
            'ogTitle' => $metaTitle,
            'ogDescription' => $metaDescription,
            'twitterTitle' => $metaTitle,
            'twitterDescription' => $metaDescription,
            'breadcrumbs' => $breadcrumbs,
            'structuredData' => $this->compactValues([
                $this->buildBreadcrumbStructuredData($breadcrumbs),
                $this->buildEntityStructuredData(
                    commodityName: $commodityName,
                    category: $category,
                    metaDescription: $metaDescription,
                    canonicalUrl: $canonicalUrl,
                    uuid: $uuid,
                    kind: $kind,
                    tier: $tier,
                    density: $density,
                    instability: $instability,
                    resistance: $resistance,
                ),
            ]),
        ];
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function buildBreadcrumbs(array $commodity, string $commodityName, string $canonicalUrl, ?string $version): array
    {
        $versionParams = $version !== null ? ['version' => $version] : [];

        $breadcrumbs = [[
            'label' => 'All Commodities',
            'url' => route('web.commodities.index', $versionParams),
        ]];

        $rawVersions = data_get($commodity, 'raw_versions', []);
        if (is_array($rawVersions) && count($rawVersions) === 1 && ($rawVersions[0]['web_url'] ?? null)) {
            $breadcrumbs[] = [
                'label' => $this->normalizeString($rawVersions[0]['name']) ?? 'Raw',
                'url' => $rawVersions[0]['web_url'],
            ];
        }

        $breadcrumbs[] = [
            'label' => $commodityName,
            'url' => $canonicalUrl,
        ];

        $refinedVersion = data_get($commodity, 'refined_version');
        if (is_array($refinedVersion) && ($refinedVersion['web_url'] ?? null)) {
            $breadcrumbs[] = [
                'label' => $this->normalizeString($refinedVersion['name']) ?? 'Refined',
                'url' => $refinedVersion['web_url'],
            ];
        }

        return $breadcrumbs;
    }

    private function buildMetaTitle(string $commodityName, ?string $kind, string|int|float|null $tier): string
    {
        $detail = $this->joinSegments([
            $kind,
            $tier !== null ? 'Tier '.$tier : null,
        ]);

        if ($detail === '') {
            $detail = 'Commodity';
        }

        return $this->joinSegments([$commodityName, $detail, 'Star Citizen'], ' | ');
    }

    private function buildFallbackDescription(string $commodityName, ?string $kind, string|int|float|null $tier): string
    {
        $base = 'Browse Star Citizen commodity data for '.$commodityName;

        $attributes = $this->compactValues([
            $kind !== null ? 'kind '.$kind : null,
            $tier !== null ? 'tier '.$tier : null,
        ]);

        if ($attributes !== []) {
            $base .= ', '.implode(', ', $attributes);
        }

        $base .= '. View locations, blueprints, and technical details.';

        return $base;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEntityStructuredData(
        string $commodityName,
        string $category,
        string $metaDescription,
        string $canonicalUrl,
        ?string $uuid,
        ?string $kind,
        string|int|float|null $tier,
        string|int|float|null $density,
        string|int|float|null $instability,
        string|int|float|null $resistance,
    ): array {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Item',
            'name' => $commodityName,
            'description' => $metaDescription,
            'url' => $canonicalUrl,
            'category' => $category,
        ];

        if ($uuid !== null) {
            $schema['sku'] = $uuid;
        }

        $additionalProperty = $this->buildPropertyValues([
            'Kind' => $kind,
            'Tier' => $tier,
            'Density' => $density,
            'Instability' => $instability,
            'Resistance' => $resistance,
        ]);

        if ($additionalProperty !== []) {
            $schema['additionalProperty'] = $additionalProperty;
        }

        return $schema;
    }

    protected function fallbackShowUrl(?string $identifier, ?string $version): string
    {
        if ($identifier === null) {
            return url()->current();
        }

        return route('web.commodities.show', array_filter([
            'identifier' => $identifier,
            'version' => $version,
        ]));
    }

    /**
     * @param  array<int, array{label: string, url: string}>  $breadcrumbs
     */
    private function resolveLeafBreadcrumbLabel(array $breadcrumbs): ?string
    {
        if (count($breadcrumbs) < 2) {
            return null;
        }

        return $this->normalizeString($breadcrumbs[count($breadcrumbs) - 2]['label'] ?? null);
    }
}
