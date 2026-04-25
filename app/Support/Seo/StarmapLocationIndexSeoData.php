<?php

declare(strict_types=1);

namespace App\Support\Seo;

class StarmapLocationIndexSeoData extends AbstractIndexSeoData
{
    protected function indexRouteName(): string
    {
        return 'web.locations.index';
    }

    protected function itemListName(): string
    {
        return 'Star Citizen Starmap Locations';
    }

    protected function metaDescription(array $data): string
    {
        return 'Browse all Star Citizen starmap locations including planets, stations, outposts, and landing zones. Filter by system, type, amenities, and more.';
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $data): array
    {
        return ['Star Citizen', 'SC', 'starmap', 'locations', 'planets', 'stations', 'outposts'];
    }

    protected function ogTitle(string $pageTitle, array $data): string
    {
        return $pageTitle.' - Star Citizen Starmap';
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    protected function breadcrumbs(string $canonicalUrl, array $versionParams, array $data): array
    {
        return [
            ['label' => 'Starmap Locations', 'url' => $canonicalUrl],
        ];
    }
}
