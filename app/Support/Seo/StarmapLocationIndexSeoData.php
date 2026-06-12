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
        return 'Planets, moons, space stations, outposts, and landing zones in all star systems. Filter by type, affiliation, and available services.';
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $entityFields): array
    {
        return ['Star Citizen', 'starmap', 'locations', 'planets', 'moons', 'space stations', 'outposts', 'landing zones'];
    }

    protected function ogTitle(string $pageTitle, array $data): string
    {
        return 'Star Citizen Starmap - Locations';
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
