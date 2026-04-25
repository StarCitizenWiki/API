<?php

declare(strict_types=1);

namespace App\Support\Seo;

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
        return 'Explore all Star Citizen vehicles including ships, ground vehicles, and gravlevs. Filter by manufacturer, career, role, and size.';
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $data): array
    {
        return ['Star Citizen', 'SC', 'vehicles', 'ships', 'ground vehicles'];
    }

    protected function ogTitle(string $pageTitle, array $data): string
    {
        return $pageTitle.' - Star Citizen Vehicles';
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    protected function breadcrumbs(string $canonicalUrl, array $versionParams, array $data): array
    {
        return [
            ['label' => 'Vehicles', 'url' => $canonicalUrl],
        ];
    }
}
