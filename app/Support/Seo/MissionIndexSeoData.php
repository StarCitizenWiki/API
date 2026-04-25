<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
        $total = Arr::get($data, 'total');
        $activeLocationFilter = $data['activeLocationFilter'] ?? null;
        $rewardScope = Arr::get($data, 'reward_scope');
        $faction = Arr::get($data, 'faction');

        $parts = [];

        if (is_numeric($total)) {
            $parts[] = 'Browse '.number_format((int) $total).' Star Citizen missions';
        } else {
            $parts[] = 'Browse all Star Citizen missions';
        }

        if ($rewardScope !== null) {
            $parts[] = 'filtered by category '.$rewardScope;
        }

        if ($faction !== null) {
            $parts[] = 'from '.$faction;
        }

        if ($activeLocationFilter !== null) {
            $parts[] = 'available at '.$activeLocationFilter['name'];
        }

        $parts[] = 'Filter by faction, type, location, legality, and more to find your next objective.';

        return Str::limit(implode('. ', $parts).'.', 160);
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $entityFields): array
    {
        $keywords = ['Star Citizen', 'SC', 'missions', 'mission guide'];
        $activeLocationFilter = $entityFields['activeLocationFilter'] ?? null;
        $rewardScope = Arr::get($entityFields, 'reward_scope');
        $faction = Arr::get($entityFields, 'faction');

        if ($activeLocationFilter !== null) {
            $keywords[] = $activeLocationFilter['name'];
            $keywords[] = 'missions at '.$activeLocationFilter['name'];
        }

        if ($rewardScope !== null) {
            $keywords[] = $rewardScope;
        }

        if ($faction !== null) {
            $keywords[] = $faction;
        }

        return array_values(array_unique(array_filter($keywords)));
    }

    protected function ogTitle(string $pageTitle, array $data): string
    {
        $rewardScope = Arr::get($data, 'reward_scope');
        $faction = Arr::get($data, 'faction');
        $activeLocationFilter = $data['activeLocationFilter'] ?? null;

        if ($rewardScope !== null) {
            return $rewardScope.' Missions - Star Citizen';
        }

        if ($faction !== null) {
            return $faction.' Missions - Star Citizen';
        }

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
        $rewardScope = Arr::get($data, 'reward_scope');
        $faction = Arr::get($data, 'faction');

        $breadcrumbs = [
            ['label' => 'Missions', 'url' => $canonicalUrl],
        ];

        $filterLabel = $rewardScope ?? $faction;

        if ($filterLabel !== null) {
            $breadcrumbs[] = ['label' => $filterLabel, 'url' => null];
        }

        if ($activeLocationFilter !== null) {
            $breadcrumbs[] = ['label' => $activeLocationFilter['name'], 'url' => null];
        }

        return $breadcrumbs;
    }
}
