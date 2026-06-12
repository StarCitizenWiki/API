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
        $massTotal = data_get($vehicle, 'mass_total');
        $health = data_get($vehicle, 'health');
        $shieldHp = data_get($vehicle, 'shield.hp');
        $maxCrew = data_get($vehicle, 'crew.max');
        $productionStatus = data_get($vehicle, 'production_status');
        if (is_array($productionStatus)) {
            $productionStatus = $productionStatus['en_EN'] ?? $productionStatus['en'] ?? reset($productionStatus) ?: null;
        }
        $quantumFuelCapacity = data_get($vehicle, 'quantum.quantum_fuel_capacity');
        $quantumRange = data_get($vehicle, 'quantum.quantum_range');
        $metaDescription = Str::limit(
            $description ?? $this->buildFallbackDescription(
                vehicleName: $vehicleName,
                manufacturerName: $manufacturerName,
                sizeClass: $sizeClass,
                role: $role,
                career: $career,
                massTotal: $massTotal,
                maxCrew: $maxCrew,
                productionStatus: $productionStatus,
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
                'Length' => data_get($vehicle, 'dimension.length'),
                'Width' => data_get($vehicle, 'dimension.width'),
                'Height' => data_get($vehicle, 'dimension.height'),
                'Mass' => $massTotal !== null ? $this->formatMass($massTotal) : null,
                'Health' => $health,
                'Shield HP' => $shieldHp,
                'Max Crew' => $maxCrew,
                'Production Status' => $productionStatus,
                'Quantum Fuel Capacity' => $quantumFuelCapacity,
                'Quantum Range' => $quantumRange,
                'Version' => data_get($vehicle, 'version'),
            ],
            brand: $manufacturerName,
        );

        if ($className !== null) {
            $vehicleSchema['vehicleConfiguration'] = $className;
        }

        $image = $this->buildImage($vehicle);
        if ($image !== null) {
            $vehicleSchema['image'] = $image;
        }

        $offers = $this->buildOffers($vehicle);
        if ($offers !== null) {
            $vehicleSchema['offers'] = $offers;
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
                $productionStatus,
                $className,
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
        ]);

        if ($detail === '') {
            return $this->pipeTitle([$leading, 'Ship & Vehicle Stats']);
        }

        return $this->pipeTitle([$leading, $detail]);
    }

    private function buildFallbackDescription(
        string $vehicleName,
        ?string $manufacturerName,
        string|int|float|null $sizeClass,
        ?string $role,
        ?string $career,
        string|int|float|null $massTotal = null,
        string|int|float|null $maxCrew = null,
        ?string $productionStatus = null,
    ): string {
        $opening = $vehicleName;

        if ($manufacturerName !== null) {
            $opening .= ' by '.$manufacturerName;
        }

        $typeInfo = $this->joinSegments([
            $sizeClass !== null ? 'size '.$sizeClass : null,
            $role,
            $career,
            'ship',
        ]);

        if ($typeInfo !== '') {
            $opening .= ', a '.$typeInfo;
        }

        $parts = [$opening];

        $stats = array_values(array_filter([
            $massTotal !== null ? $this->formatMass($massTotal) : null,
            $maxCrew !== null ? 'max crew '.$maxCrew : null,
            $productionStatus !== null ? strtolower($productionStatus) : null,
        ], static fn (mixed $v): bool => $v !== null && $v !== ''));

        if ($stats !== []) {
            $parts[] = implode(', ', $stats);
        }

        return implode('. ', $parts).'.';
    }

    private function buildOffers(array $vehicle): ?array
    {
        $allOffers = [];

        $msrp = data_get($vehicle, 'msrp');
        if ($msrp !== null && (is_int($msrp) || is_float($msrp)) && $msrp > 0) {
            $msrpOffer = [
                '@type' => 'Offer',
                'price' => $msrp,
                'priceCurrency' => 'USD',
                'availability' => 'https://schema.org/InStock',
            ];

            $pledgeUrl = data_get($vehicle, 'pledge_url');
            if ($pledgeUrl !== null) {
                $msrpOffer['url'] = $pledgeUrl;
            }

            $allOffers[] = [
                '@type' => 'AggregateOffer',
                'priceCurrency' => 'USD',
                'lowPrice' => $msrp,
                'highPrice' => $msrp,
                'offerCount' => 1,
                'offers' => [$msrpOffer],
            ];
        }

        $uexPurchasePrices = data_get($vehicle, 'uex_prices.purchase');
        if (is_array($uexPurchasePrices) && $uexPurchasePrices !== []) {
            $uexOffer = $this->buildUexAggregateOffer($uexPurchasePrices);
            if ($uexOffer !== null) {
                $allOffers[] = $uexOffer;
            }
        }

        if ($allOffers === []) {
            return null;
        }

        if (count($allOffers) === 1) {
            return $allOffers[0];
        }

        return $allOffers;
    }
}
