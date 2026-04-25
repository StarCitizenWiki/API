<?php

declare(strict_types=1);

namespace App\Support\Seo;

class MissionIndexSeoData extends AbstractIndexSeoData
{
    protected function indexRouteName(): string
    {
        return 'web.missions.index';
    }

    protected function itemListName(): string
    {
        return 'Star Citizen Missions';
    }

    protected function metaDescription(array $data): string
    {
        $activeLocationFilter = $data['activeLocationFilter'] ?? null;

        if ($activeLocationFilter !== null) {
            $name = $activeLocationFilter['name'];

            return "Browse Star Citizen missions available at {$name}. Filter by faction, type, legality, and more to find your next objective.";
        }

        return 'Browse all Star Citizen missions. Filter by faction, type, location, legality, and more to find your next objective.';
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $data): array
    {
        $keywords = ['Star Citizen', 'SC', 'missions', 'mission guide'];
        $activeLocationFilter = $data['activeLocationFilter'] ?? null;

        if ($activeLocationFilter !== null) {
            $keywords[] = $activeLocationFilter['name'];
            $keywords[] = 'missions at '.$activeLocationFilter['name'];
        }

        return $keywords;
    }

    protected function ogTitle(string $pageTitle, array $data): string
    {
        $activeLocationFilter = $data['activeLocationFilter'] ?? null;

        if ($activeLocationFilter !== null) {
            return 'Star Citizen Missions at '.$activeLocationFilter['name'];
        }

        return 'Star Citizen Missions';
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    protected function breadcrumbs(string $canonicalUrl, array $versionParams, array $data): array
    {
        $activeLocationFilter = $data['activeLocationFilter'] ?? null;

        $breadcrumbs = [
            ['label' => 'Missions', 'url' => $canonicalUrl],
        ];

        if ($activeLocationFilter !== null) {
            $breadcrumbs[] = ['label' => $activeLocationFilter['name'], 'url' => null];
        }

        return $breadcrumbs;
    }
}
