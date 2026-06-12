<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class StarmapLocationShowSeoData extends AbstractShowSeoData
{
    protected function baseKeywords(): array
    {
        return ['Star Citizen', 'Starmap'];
    }

    protected function showRouteName(): string
    {
        return 'web.locations.show';
    }

    /**
     * @return array<string, mixed>
     */
    public function build(array $location, Request $request): array
    {
        $locationName = data_get($location, 'name') ?? 'Starmap Location';
        $typeName = data_get($location, 'Type.Name')
            ?? data_get($location, 'type_name');
        $classification = data_get($location, 'Type.Classification')
            ?? data_get($location, 'type_classification');
        $description = $this->resolveDescription(data_get($location, 'description'));
        $starName = data_get($location, 'star.name');
        $starSlug = data_get($location, 'star.slug');
        $starUuid = data_get($location, 'star.uuid');
        $parentName = data_get($location, 'parent.name');
        $parentSlug = data_get($location, 'parent.slug');
        $parentUuid = data_get($location, 'parent.uuid');
        $uuid = data_get($location, 'uuid');
        $slug = data_get($location, 'slug');
        $version = $this->resolveVersionCode($request);
        $canonicalUrl = data_get($location, 'web_url')
            ?? $this->fallbackShowUrl($slug ?? $uuid, $version);
        $breadcrumbs = $this->buildBreadcrumbs(
            locationName: $locationName,
            canonicalUrl: $canonicalUrl,
            starName: $starName,
            starIdentifier: $starSlug ?? $starUuid,
            starUuid: $starUuid,
            parentName: $parentName,
            parentIdentifier: $parentSlug ?? $parentUuid,
            parentUuid: $parentUuid,
            locationUuid: $uuid,
            version: $version,
        );
        $metaTitle = $this->pipeTitle([$locationName, $typeName], 'Star Citizen Starmap');
        $metaDescription = Str::limit(
            $description ?? $this->buildFallbackDescription(
                locationName: $locationName,
                typeName: $typeName,
                classification: $classification,
                starName: $starName,
                parentName: $parentName,
                childCount: data_get($location, 'child_count'),
            ),
            160,
        );

        return $this->buildSeoResponse(
            canonicalUrl: $canonicalUrl,
            metaDescription: $metaDescription,
            keywords: $this->keywords([
                $locationName,
                $typeName,
                $classification,
                data_get($location, 'Affiliation.Name'),
                data_get($location, 'Jurisdiction.Name'),
                $starName,
                $parentName,
            ]),
            ogTitle: $metaTitle,
            breadcrumbs: $breadcrumbs,
            structuredData: [
                $this->buildBreadcrumbStructuredData($breadcrumbs),
                $this->buildPlaceStructuredData($location, $locationName, $metaDescription, $canonicalUrl),
            ],
            title: $metaTitle,
        );
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function buildBreadcrumbs(
        string $locationName,
        string $canonicalUrl,
        ?string $starName,
        ?string $starIdentifier,
        ?string $starUuid,
        ?string $parentName,
        ?string $parentIdentifier,
        ?string $parentUuid,
        ?string $locationUuid,
        ?string $version,
    ): array {
        $versionParams = $version !== null ? ['version' => $version] : [];
        $breadcrumbs = [[
            'label' => 'All Locations',
            'url' => route('web.locations.index', $versionParams),
        ]];

        $ancestorName = null;
        $ancestorIdentifier = null;

        if ($starName !== null && $starIdentifier !== null && $starUuid !== $locationUuid) {
            $ancestorName = $starName;
            $ancestorIdentifier = $starIdentifier;
        }

        if ($ancestorName !== null && $ancestorIdentifier !== null) {
            $breadcrumbs[] = [
                'label' => $ancestorName,
                'url' => route('web.locations.show', array_merge(['identifier' => $ancestorIdentifier], $versionParams)),
            ];
        }

        if (
            $parentName !== null
            && $parentIdentifier !== null
            && $parentUuid !== null
            && $parentUuid !== $locationUuid
            && $parentUuid !== $starUuid
        ) {
            $breadcrumbs[] = [
                'label' => $parentName,
                'url' => route('web.locations.show', array_merge(['identifier' => $parentIdentifier], $versionParams)),
            ];
        }

        $breadcrumbs[] = [
            'label' => $locationName,
            'url' => $canonicalUrl,
        ];

        return $breadcrumbs;
    }

    private function buildFallbackDescription(
        string $locationName,
        ?string $typeName,
        ?string $classification,
        ?string $starName,
        ?string $parentName,
        mixed $childCount,
    ): string {
        $opening = $locationName;

        $detail = $this->joinSegments([$typeName, $classification]);
        if ($detail !== '') {
            $opening .= ', a '.$detail;
        }

        $parts = [$opening];

        $context = array_values(array_filter([
            $parentName !== null ? 'orbiting '.$parentName : null,
            $starName !== null ? 'in the '.$starName.' system' : null,
        ], static fn (mixed $v): bool => $v !== null && $v !== ''));

        if ($context !== []) {
            $parts[] = implode(' ', $context);
        }

        if ($childCount !== null && $childCount > 0) {
            $parts[] = $childCount.' connected locations';
        }

        return implode('. ', $parts).'.';
    }

    /**
     * @return array<string, mixed>
     */
    private function buildPlaceStructuredData(
        array $location,
        string $locationName,
        string $metaDescription,
        string $canonicalUrl,
    ): array {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Place',
            'name' => $locationName,
            'description' => $metaDescription,
            'url' => $canonicalUrl,
        ];

        $additionalProperty = $this->buildPropertyValues([
            'Type' => data_get($location, 'Type.Name')
                ?? data_get($location, 'type_name'),
            'Classification' => data_get($location, 'Type.Classification')
                ?? data_get($location, 'type_classification'),
            'Tag' => data_get($location, 'tag.name'),
            'Affiliation' => data_get($location, 'Affiliation.Name'),
            'Jurisdiction' => data_get($location, 'Jurisdiction.Name'),
            'Respawn Location Type' => data_get($location, 'respawn_location_type'),
            'Version' => data_get($location, 'version'),
            'Child Count' => data_get($location, 'child_count'),
        ]);

        if ($additionalProperty !== []) {
            $schema['additionalProperty'] = $additionalProperty;
        }

        $containedInPlace = $this->buildContainedInPlace($location);
        if ($containedInPlace !== null) {
            $schema['containedInPlace'] = $containedInPlace;
        }

        $amenityFeatures = $this->buildAmenityFeatures(data_get($location, 'amenities', []));
        if ($amenityFeatures !== []) {
            $schema['amenityFeature'] = $amenityFeatures;
        }

        $image = $this->buildImage($location);
        if ($image !== null) {
            $schema['image'] = $image;
        }

        return $schema;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildContainedInPlace(array $location): ?array
    {
        $parentName = data_get($location, 'parent.name');
        $parentUuid = data_get($location, 'parent.uuid');

        if ($parentName === null || $parentUuid === null) {
            return null;
        }

        $containedIn = [
            '@type' => 'Place',
            'name' => $parentName,
        ];

        $parentTypeName = data_get($location, 'parent.type_name');
        if ($parentTypeName !== null) {
            $containedIn['@type'] = $parentTypeName;
        }

        $starName = data_get($location, 'star.name');
        if ($starName !== null) {
            $containedIn['containedInPlace'] = [
                '@type' => 'Place',
                'name' => $starName,
            ];

            $system = data_get($location, 'system');
            if ($system !== null) {
                $containedIn['containedInPlace']['containedInPlace'] = [
                    '@type' => 'Place',
                    'name' => $system,
                ];
            }
        }

        return $containedIn;
    }

    /**
     * @param  array<int, array{name: string, display_name: string|null}>  $amenities
     * @return array<int, array<string, mixed>>
     */
    private function buildAmenityFeatures(array $amenities): array
    {
        if ($amenities === []) {
            return [];
        }

        $features = [];

        foreach ($amenities as $amenity) {
            $name = data_get($amenity, 'display_name')
                ?? data_get($amenity, 'name');

            if ($name === null) {
                continue;
            }

            $features[] = [
                '@type' => 'LocationFeatureSpecification',
                'name' => $name,
                'value' => true,
            ];
        }

        return $features;
    }

    protected function fallbackShowUrl(?string $uuid, ?string $version): string
    {
        $routeParameters = ['identifier' => $uuid ?? 'starmap-location'];

        if ($version !== null) {
            $routeParameters['version'] = $version;
        }

        return route($this->showRouteName(), $routeParameters);
    }
}
