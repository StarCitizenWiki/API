<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Support\Format;
use Illuminate\Support\Arr;

class VehicleIndexSeoData extends AbstractIndexSeoData
{
    protected function indexRouteName(): string
    {
        return 'web.vehicles.index';
    }

    protected function itemListName(): string
    {
        return 'Star Citizen Vehicles';
    }

    protected function metaDescription(array $data): string
    {
        $total = Arr::get($data, 'total');
        $manufacturer = Arr::get($data, 'manufacturer');
        $count = is_numeric($total) ? Format::number((int) $total).' ' : '';

        if ($manufacturer !== null) {
            return "{$count}{$manufacturer} ships and vehicles. Full stats, component loadouts, and variants.";
        }

        return "{$count}ships and ground vehicles. Compare speed, cargo, crew, components, and insurance for every flyable ship.";
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $entityFields): array
    {
        $keywords = ['Star Citizen', 'ships', 'vehicles', 'ground vehicles', 'ship stats'];
        $manufacturer = Arr::get($entityFields, 'manufacturer');

        if ($manufacturer !== null) {
            $keywords[] = $manufacturer;
            $keywords[] = $manufacturer.' ships';
        }

        return $keywords
                |> array_filter(...)
                |> array_unique(...)
                |> array_values(...);
    }

    protected function ogTitle(string $pageTitle, array $data): string
    {
        $manufacturer = Arr::get($data, 'manufacturer');

        if ($manufacturer !== null) {
            return $manufacturer.' Vehicles - Star Citizen Wiki';
        }

        return 'Star Citizen Ships & Vehicles';
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    protected function breadcrumbs(string $canonicalUrl, array $versionParams, array $data): array
    {
        $manufacturer = Arr::get($data, 'manufacturer');

        $breadcrumbs = [
            ['label' => 'Vehicles', 'url' => $canonicalUrl],
        ];

        if ($manufacturer !== null) {
            $breadcrumbs[] = [
                'label' => $manufacturer,
                'url' => null,
            ];
        }

        return $breadcrumbs;
    }
}
