<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class VehicleShowSeoData extends AbstractShowSeoData
{
    /**
     * @return array<string, mixed>
     */
    public function build(array $vehicle, Request $request): array
    {
        $vehicleName = $this->normalizeString(data_get($vehicle, 'name')) ?? 'Vehicle';
        $manufacturerName = $this->normalizeString(data_get($vehicle, 'manufacturer.name'));
        $manufacturerCode = $this->normalizeString(data_get($vehicle, 'manufacturer.code'));
        $sizeClass = $this->normalizeScalar(data_get($vehicle, 'size_class'));
        $career = $this->normalizeString(data_get($vehicle, 'career'));
        $role = $this->normalizeString(data_get($vehicle, 'role'));
        $className = $this->normalizeString(data_get($vehicle, 'class_name'));
        $description = $this->resolveDescription(data_get($vehicle, 'description'));
        $canonicalUrl = $this->normalizeString(data_get($vehicle, 'web_url'))
            ?? $this->fallbackShowUrl(
                identifier: $this->normalizeString(data_get($vehicle, 'slug')) ?? $this->normalizeString(data_get($vehicle, 'uuid')),
                version: $this->resolveVersionCode($request),
            );
        $breadcrumbs = $this->buildBreadcrumbs(
            manufacturerName: $manufacturerName,
            manufacturerCode: $manufacturerCode,
            vehicleName: $vehicleName,
            canonicalUrl: $canonicalUrl,
            version: $this->resolveVersionCode($request),
        );
        $metaTitle = $this->buildMetaTitle($vehicleName, $manufacturerName, $sizeClass, $role);
        $metaDescription = Str::limit(
            $description ?? $this->buildFallbackDescription(
                vehicleName: $vehicleName,
                manufacturerName: $manufacturerName,
                sizeClass: $sizeClass,
                role: $role,
                career: $career,
            ),
            160,
        );
        $category = $this->joinSegments([
            $sizeClass !== null ? 'Size '.$sizeClass : null,
            $career,
            $role,
            'Vehicle',
        ]);

        if ($category === '') {
            $category = 'Star Citizen Vehicle';
        }

        return [
            'title' => $metaTitle,
            'metaDescription' => $metaDescription,
            'canonicalUrl' => $canonicalUrl,
            'keywords' => $this->compactValues([
                $vehicleName,
                $manufacturerName,
                $sizeClass !== null ? 'Size '.$sizeClass : null,
                $career,
                $role,
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
                    vehicleName: $vehicleName,
                    manufacturerName: $manufacturerName,
                    category: $category,
                    metaDescription: $metaDescription,
                    canonicalUrl: $canonicalUrl,
                    className: $className,
                    manufacturerCode: $manufacturerCode,
                    sizeClass: $sizeClass,
                    career: $career,
                    role: $role,
                    crewMin: $this->normalizeScalar(data_get($vehicle, 'crew.min')),
                    cargoCapacity: $this->normalizeScalar(data_get($vehicle, 'cargo_capacity')),
                    scmSpeed: $this->normalizeScalar(data_get($vehicle, 'speed.scm')),
                    maxSpeed: $this->normalizeScalar(data_get($vehicle, 'speed.max')),
                    quantumSpeed: $this->normalizeScalar(data_get($vehicle, 'quantum.quantum_speed')),
                    version: $this->normalizeString(data_get($vehicle, 'version')),
                ),
            ]),
        ];
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function buildBreadcrumbs(
        ?string $manufacturerName,
        ?string $manufacturerCode,
        string $vehicleName,
        string $canonicalUrl,
        ?string $version,
    ): array {
        $versionParams = $version !== null ? ['version' => $version] : [];
        $manufacturerFilter = $manufacturerCode ?? $manufacturerName;
        $breadcrumbs = [[
            'label' => 'All Vehicles',
            'url' => route('web.vehicles.index', $versionParams),
        ]];

        if ($manufacturerName !== null && $manufacturerFilter !== null) {
            $breadcrumbs[] = [
                'label' => $manufacturerName,
                'url' => route('web.vehicles.index', array_merge($versionParams, [
                    'filter' => ['manufacturer' => $manufacturerFilter],
                ])),
            ];
        }

        $breadcrumbs[] = [
            'label' => $vehicleName,
            'url' => $canonicalUrl,
        ];

        return $breadcrumbs;
    }

    private function buildMetaTitle(
        string $vehicleName,
        ?string $manufacturerName,
        string|int|float|null $sizeClass,
        ?string $role,
    ): string {
        $leading = $manufacturerName !== null
            ? $vehicleName.' by '.$manufacturerName
            : $vehicleName;
        $detail = $this->joinSegments([
            $sizeClass !== null ? 'Size '.$sizeClass : null,
            $role,
            'Vehicle',
        ]);

        if ($detail === '') {
            $detail = 'Vehicle';
        }

        return $this->joinSegments([$leading, $detail, 'Star Citizen'], ' | ');
    }

    private function buildFallbackDescription(
        string $vehicleName,
        ?string $manufacturerName,
        string|int|float|null $sizeClass,
        ?string $role,
        ?string $career,
    ): string {
        $description = 'Browse Star Citizen vehicle data for '.$vehicleName;

        if ($manufacturerName !== null) {
            $description .= ' by '.$manufacturerName;
        }

        $attributes = $this->compactValues([
            $sizeClass !== null ? 'size '.$sizeClass : null,
            $role !== null ? 'role '.$role : null,
            $career !== null ? 'career '.$career : null,
        ]);

        if ($attributes !== []) {
            $description .= ', '.implode(', ', $attributes);
        }

        $description .= '. View cargo, crew, speed, quantum, signatures, and insurance data.';

        return $description;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEntityStructuredData(
        string $vehicleName,
        ?string $manufacturerName,
        string $category,
        string $metaDescription,
        string $canonicalUrl,
        ?string $className,
        ?string $manufacturerCode,
        string|int|float|null $sizeClass,
        ?string $career,
        ?string $role,
        string|int|float|null $crewMin,
        string|int|float|null $cargoCapacity,
        string|int|float|null $scmSpeed,
        string|int|float|null $maxSpeed,
        string|int|float|null $quantumSpeed,
        ?string $version,
    ): array {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Vehicle',
            'name' => $vehicleName,
            'description' => $metaDescription,
            'url' => $canonicalUrl,
            'category' => $category,
        ];

        if ($manufacturerName !== null) {
            $schema['brand'] = [
                '@type' => 'Brand',
                'name' => $manufacturerName,
            ];
        }

        if ($className !== null) {
            $schema['vehicleConfiguration'] = $className;
        }

        $additionalProperty = $this->buildPropertyValues([
            'Manufacturer Code' => $manufacturerCode,
            'Size Class' => $sizeClass,
            'Career' => $career,
            'Role' => $role,
            'Crew' => $crewMin,
            'Cargo Capacity' => $cargoCapacity,
            'SCM Speed' => $scmSpeed,
            'Max Speed' => $maxSpeed,
            'Quantum Speed' => $quantumSpeed,
            'Version' => $version,
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

        return route('web.vehicles.show', array_filter([
            'vehicle' => $identifier,
            'version' => $version,
        ]));
    }
}
