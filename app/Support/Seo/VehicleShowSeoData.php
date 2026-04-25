<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class VehicleShowSeoData extends AbstractShowSeoData
{
    protected function showRouteName(): string
    {
        return 'web.vehicles.show';
    }

    protected function showRouteParameterName(): string
    {
        return 'vehicle';
    }

    /**
     * @return array<string, mixed>
     */
    public function build(array $vehicle, Request $request): array
    {
        $vehicleName = data_get($vehicle, 'name') ?? 'Vehicle';
        $manufacturerName = ($v = data_get($vehicle, 'manufacturer.name')) !== null ? trim(html_entity_decode($v)) : null;
        $manufacturerCode = data_get($vehicle, 'manufacturer.code');
        $sizeClass = data_get($vehicle, 'size_class');
        $career = data_get($vehicle, 'career');
        $role = data_get($vehicle, 'role');
        $className = data_get($vehicle, 'class_name');
        $description = $this->resolveDescription(data_get($vehicle, 'description'));
        $canonicalUrl = data_get($vehicle, 'web_url')
            ?? $this->fallbackShowUrl(
                identifier: data_get($vehicle, 'slug') ?? data_get($vehicle, 'uuid'),
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

        $vehicleSchema = $this->buildBaseEntityStructuredData(
            schemaType: 'Vehicle',
            name: $vehicleName,
            description: $metaDescription,
            url: $canonicalUrl,
            category: $category,
            additionalProperties: [
                'Manufacturer Code' => $manufacturerCode,
                'Size Class' => $sizeClass,
                'Career' => $career,
                'Role' => $role,
                'Crew' => data_get($vehicle, 'crew.min'),
                'Cargo Capacity' => data_get($vehicle, 'cargo_capacity'),
                'SCM Speed' => data_get($vehicle, 'speed.scm'),
                'Max Speed' => data_get($vehicle, 'speed.max'),
                'Quantum Speed' => data_get($vehicle, 'quantum.quantum_speed'),
                'Version' => data_get($vehicle, 'version'),
            ],
        );

        if ($manufacturerName !== null) {
            $vehicleSchema['brand'] = [
                '@type' => 'Brand',
                'name' => $manufacturerName,
            ];
        }

        if ($className !== null) {
            $vehicleSchema['vehicleConfiguration'] = $className;
        }

        return $this->buildSeoResponse(
            canonicalUrl: $canonicalUrl,
            metaDescription: $metaDescription,
            keywords: $this->keywords([
                $vehicleName,
                $manufacturerName,
                $sizeClass !== null ? 'Size '.$sizeClass : null,
                $career,
                $role,
            ]),
            ogTitle: $metaTitle,
            breadcrumbs: $breadcrumbs,
            structuredData: [
                $this->buildBreadcrumbStructuredData($breadcrumbs),
                $vehicleSchema,
            ],
            title: $metaTitle,
        );
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

        return $this->pipeTitle([$leading, $detail]);
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

        $attributes = array_values(array_filter([
            $sizeClass !== null ? 'size '.$sizeClass : null,
            $role !== null ? 'role '.$role : null,
            $career !== null ? 'career '.$career : null,
        ], static fn (mixed $v): bool => $v !== null && $v !== ''));

        if ($attributes !== []) {
            $description .= ', '.implode(', ', $attributes);
        }

        $description .= '. View cargo, crew, speed, quantum, signatures, and insurance data.';

        return $description;
    }
}
