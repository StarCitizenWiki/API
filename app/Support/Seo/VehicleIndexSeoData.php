<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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

        $count = is_numeric($total) ? number_format((int) $total).' ' : '';

        $parts = ["Explore the complete {$count}Star Citizen vehicles database"];

        if ($manufacturer !== null) {
            $parts[] = 'from '.$manufacturer;
        }

        $parts[] = 'including ships, ground vehicles, and gravlevs. Filter by manufacturer, career, role, and size.';

        return Str::limit(implode('. ', $parts).'.', 160);
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $entityFields): array
    {
        $keywords = ['Star Citizen', 'SC', 'vehicles', 'ships', 'ground vehicles'];
        $manufacturer = Arr::get($entityFields, 'manufacturer');

        if ($manufacturer !== null) {
            $keywords[] = $manufacturer;
        }

        return array_values(array_unique(array_filter($keywords)));
    }

    protected function ogTitle(string $pageTitle, array $data): string
    {
        $manufacturer = Arr::get($data, 'manufacturer');

        if ($manufacturer !== null) {
            return $manufacturer.' Vehicles - Star Citizen Vehicles';
        }

        return $pageTitle.' - Star Citizen Vehicles';
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
