<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class StarmapLocationShowSeoData extends AbstractShowSeoData
{
    /**
     * @return array<string, mixed>
     */
    public function build(array $location, Request $request): array
    {
        $locationName = $this->normalizeString(data_get($location, 'name')) ?? 'Starmap Location';
        $typeName = $this->normalizeString(data_get($location, 'Type.Name'))
            ?? $this->normalizeString(data_get($location, 'type_name'));
        $classification = $this->normalizeString(data_get($location, 'Type.Classification'))
            ?? $this->normalizeString(data_get($location, 'type_classification'));
        $description = $this->resolveDescription(data_get($location, 'description'));
        $starName = $this->normalizeString(data_get($location, 'star.name'));
        $starUuid = $this->normalizeString(data_get($location, 'star.uuid'));
        $parentName = $this->normalizeString(data_get($location, 'parent.name'));
        $parentUuid = $this->normalizeString(data_get($location, 'parent.uuid'));
        $uuid = $this->normalizeString(data_get($location, 'uuid'));
        $version = $this->resolveVersionCode($request);
        $canonicalUrl = $this->normalizeString(data_get($location, 'web_url'))
            ?? $this->fallbackShowUrl($uuid, $version);
        $breadcrumbs = $this->buildBreadcrumbs(
            locationName: $locationName,
            locationUuid: $uuid,
            canonicalUrl: $canonicalUrl,
            starName: $starName,
            starUuid: $starUuid,
            parentName: $parentName,
            parentUuid: $parentUuid,
            version: $version,
        );
        $metaTitle = $this->joinSegments([$locationName, $typeName, 'Star Citizen Starmap'], ' | ');
        $metaDescription = Str::limit(
            $description ?? $this->buildFallbackDescription(
                locationName: $locationName,
                typeName: $typeName,
                classification: $classification,
                starName: $starName,
                parentName: $parentName,
                childCount: $this->normalizeInt(data_get($location, 'child_count')),
            ),
            160,
        );

        return [
            'title' => $metaTitle,
            'metaDescription' => $metaDescription,
            'canonicalUrl' => $canonicalUrl,
            'keywords' => $this->compactValues([
                $locationName,
                $typeName,
                $classification,
                $this->normalizeString(data_get($location, 'Affiliation.Name')),
                $this->normalizeString(data_get($location, 'Jurisdiction.Name')),
                $starName,
                $parentName,
                'Star Citizen',
                'Starmap',
            ]),
            'ogTitle' => $metaTitle,
            'ogDescription' => $metaDescription,
            'twitterTitle' => $metaTitle,
            'twitterDescription' => $metaDescription,
            'breadcrumbs' => $breadcrumbs,
            'structuredData' => $this->compactValues([
                $this->buildBreadcrumbStructuredData($breadcrumbs),
                $this->buildPlaceStructuredData($location, $locationName, $metaDescription, $canonicalUrl),
            ]),
        ];
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function buildBreadcrumbs(
        string $locationName,
        ?string $locationUuid,
        string $canonicalUrl,
        ?string $starName,
        ?string $starUuid,
        ?string $parentName,
        ?string $parentUuid,
        ?string $version,
    ): array {
        $versionParams = $version !== null ? ['version' => $version] : [];
        $breadcrumbs = [[
            'label' => 'All Locations',
            'url' => route('web.locations.index', $versionParams),
        ]];

        $ancestorName = null;
        $ancestorUuid = null;

        if ($starName !== null && $starUuid !== null && $starUuid !== $locationUuid) {
            $ancestorName = $starName;
            $ancestorUuid = $starUuid;
        }

        if ($ancestorName !== null && $ancestorUuid !== null) {
            $breadcrumbs[] = [
                'label' => $ancestorName,
                'url' => route('web.locations.show', array_merge(['identifier' => $ancestorUuid], $versionParams)),
            ];
        }

        if (
            $parentName !== null
            && $parentUuid !== null
            && $parentUuid !== $locationUuid
            && $parentUuid !== $ancestorUuid
        ) {
            $breadcrumbs[] = [
                'label' => $parentName,
                'url' => route('web.locations.show', array_merge(['identifier' => $parentUuid], $versionParams)),
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
        ?int $childCount,
    ): string {
        $description = 'Browse Star Citizen starmap data for '.$locationName;

        $detail = $this->joinSegments([$typeName, $classification]);

        if ($detail !== '') {
            $description .= ', a '.$detail;
        }

        $context = $this->compactValues([
            $parentName !== null ? 'within '.$parentName : null,
            $starName !== null ? 'in the '.$starName.' system' : null,
        ]);

        if ($context !== []) {
            $description .= ' '.implode(' ', $context);
        }

        if ($childCount !== null && $childCount > 0) {
            $description .= '. Explore '.$childCount.' connected child locations';
        } else {
            $description .= '. View hierarchy, amenities, and technical details';
        }

        $description .= '.';

        return $description;
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
            'Type' => $this->normalizeString(data_get($location, 'Type.Name'))
                ?? $this->normalizeString(data_get($location, 'type_name')),
            'Classification' => $this->normalizeString(data_get($location, 'Type.Classification'))
                ?? $this->normalizeString(data_get($location, 'type_classification')),
            'Tag' => $this->normalizeString(data_get($location, 'tag.name')),
            'Affiliation' => $this->normalizeString(data_get($location, 'Affiliation.Name')),
            'Jurisdiction' => $this->normalizeString(data_get($location, 'Jurisdiction.Name')),
            'Respawn Location Type' => $this->normalizeString(data_get($location, 'respawn_location_type')),
            'Version' => $this->normalizeString(data_get($location, 'version')),
            'Child Count' => $this->normalizeInt(data_get($location, 'child_count')),
        ]);

        if ($additionalProperty !== []) {
            $schema['additionalProperty'] = $additionalProperty;
        }

        return $schema;
    }

    protected function fallbackShowUrl(?string $uuid, ?string $version): string
    {
        $routeParameters = ['identifier' => $uuid ?? 'starmap-location'];

        if ($version !== null) {
            $routeParameters['version'] = $version;
        }

        return route('web.locations.show', $routeParameters);
    }

    protected function resolveDescription(mixed $description): ?string
    {
        if (! is_string($description)) {
            return null;
        }

        $description = trim(html_entity_decode($description));

        return $description === '' ? null : $description;
    }

    protected function resolveVersionCode(Request $request): ?string
    {
        return $this->normalizeString($request->query('version'));
    }

    /**
     * @param  array<string, string|int|float|null>  $properties
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
                'value' => (string) $value,
            ];
        }

        return $values;
    }

    protected function joinSegments(array $segments, string $separator = ' '): string
    {
        $segments = $this->compactValues($segments);

        return implode($separator, $segments);
    }
}
