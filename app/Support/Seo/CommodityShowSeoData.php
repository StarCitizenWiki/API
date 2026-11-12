<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class CommodityShowSeoData extends AbstractShowSeoData
{
    protected function showRouteName(): string
    {
        return 'web.commodities.show';
    }

    /**
     * @return array<string, mixed>
     */
    public function build(array $commodity, Request $request): array
    {
        $commodityName = data_get($commodity, 'name') ?? 'Commodity';
        $slug = data_get($commodity, 'slug');
        $uuid = data_get($commodity, 'uuid');
        $kind = data_get($commodity, 'kind');
        $tier = data_get($commodity, 'tier');
        $density = data_get($commodity, 'density');
        $instability = data_get($commodity, 'instability');
        $resistance = data_get($commodity, 'resistance');
        $description = $this->resolveDescription(data_get($commodity, 'description'));
        $version = $this->resolveVersionCode($request);
        $canonicalUrl = data_get($commodity, 'web_url')
            ?? $this->fallbackShowUrl($slug ?? $uuid, $version);
        $breadcrumbs = $this->buildBreadcrumbs($commodity, $commodityName, $canonicalUrl, $version);

        $metaTitle = $this->buildMetaTitle($commodityName, $kind, $tier);
        $metaDescription = Str::limit(
            $description ?? $this->buildFallbackDescription($commodityName, $kind, $tier),
            160,
        );

        $category = $kind
            ?? $this->resolveLeafBreadcrumbLabel($breadcrumbs)
            ?? 'Star Citizen Commodity';

        $commoditySchema = $this->buildBaseEntityStructuredData(
            schemaType: 'Item',
            name: $commodityName,
            description: $metaDescription,
            url: $canonicalUrl,
            category: $category,
            additionalProperties: [
                'Kind' => $kind,
                'Tier' => $tier,
                'Density' => $density,
                'Instability' => $instability,
                'Resistance' => $resistance,
            ],
        );

        if ($uuid !== null) {
            $commoditySchema['sku'] = $uuid;
        }

        $image = $this->buildImage($commodity);
        if ($image !== null) {
            $commoditySchema['image'] = $image;
        }

        $refinedRelation = $this->buildRefinedVersionRelation($commodity);
        if ($refinedRelation !== null) {
            $commoditySchema['isRelatedTo'] = $refinedRelation;
        }

        return $this->buildSeoResponse(
            canonicalUrl: $canonicalUrl,
            metaDescription: $metaDescription,
            keywords: $this->keywords([
                $commodityName,
                $kind,
                $tier !== null ? 'Tier '.$tier : null,
            ]),
            ogTitle: $metaTitle,
            breadcrumbs: $breadcrumbs,
            structuredData: [
                $this->buildBreadcrumbStructuredData($breadcrumbs),
                $commoditySchema,
            ],
            title: $metaTitle,
        );
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
                'label' => $rawVersions[0]['name'] ?? 'Raw',
                'url' => $rawVersions[0]['web_url'],
            ];
        }

        $breadcrumbs[] = [
            'label' => $commodityName,
            'url' => $canonicalUrl,
        ];

        return $breadcrumbs;
    }

    /**
     * The refined commodity is a sibling page, not a trail ancestor; link it
     * from the entity schema so the current page stays the breadcrumb leaf.
     *
     * @param  array<string, mixed>  $commodity
     * @return array<string, mixed>|null
     */
    private function buildRefinedVersionRelation(array $commodity): ?array
    {
        $refinedVersion = data_get($commodity, 'refined_version');
        if (! is_array($refinedVersion) || ! is_string($refinedVersion['web_url'] ?? null)) {
            return null;
        }

        $relation = [
            '@type' => 'Thing',
            'name' => $refinedVersion['name'] ?? 'Refined',
            'url' => $refinedVersion['web_url'],
        ];

        $uuid = $refinedVersion['uuid'] ?? null;
        if (is_string($uuid) && $uuid !== '') {
            $relation['identifier'] = $uuid;
        }

        return $relation;
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

        return $this->pipeTitle([$commodityName, $detail]);
    }

    private function buildFallbackDescription(string $commodityName, ?string $kind, string|int|float|null $tier): string
    {
        $opening = $commodityName;

        $typeInfo = $this->joinSegments([
            $tier !== null ? 'tier '.$tier : null,
            $kind,
            'commodity',
        ]);

        if ($typeInfo !== '') {
            $opening .= ', a '.$typeInfo;
        }

        $parts = [$opening];
        $parts[] = 'Find where to mine or buy it, what it refines into, and which blueprints use it.';

        return implode('. ', $parts).'.';
    }
}
