<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Support\Format;
use Illuminate\Support\Arr;

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

        $count = is_numeric($total) ? Format::number((int) $total).' ' : '';

        if ($activeLocationFilter !== null) {
            $prefix = "{$count}missions available at {$activeLocationFilter['name']}";
        } elseif ($faction !== null) {
            $prefix = "{$count}{$faction} missions";
        } elseif ($rewardScope !== null) {
            $prefix = "{$count}{$rewardScope} missions";
        } else {
            $prefix = "{$count}missions";
        }

        return "{$prefix}. Payouts, objectives, faction details, and prerequisites.";
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $entityFields): array
    {
        $keywords = ['Star Citizen', 'missions', 'mission guide', 'mission rewards'];
        $activeLocationFilter = $entityFields['activeLocationFilter'] ?? null;
        $rewardScope = Arr::get($entityFields, 'reward_scope');
        $faction = Arr::get($entityFields, 'faction');

        if ($activeLocationFilter !== null) {
            $keywords[] = $activeLocationFilter['name'];
        }

        if ($rewardScope !== null) {
            $keywords[] = $rewardScope;
            $keywords[] = $rewardScope.' missions';
        }

        if ($faction !== null) {
            $keywords[] = $faction;
            $keywords[] = $faction.' missions';
        }

        return $keywords
                |> array_filter(...)
                |> array_unique(...)
                |> array_values(...);
    }

    protected function ogTitle(string $pageTitle, array $data): string
    {
        $rewardScope = Arr::get($data, 'reward_scope');
        $faction = Arr::get($data, 'faction');
        $activeLocationFilter = $data['activeLocationFilter'] ?? null;

        if ($rewardScope !== null) {
            return $rewardScope.' Missions - Star Citizen Wiki';
        }

        if ($faction !== null) {
            return $faction.' Missions - Star Citizen Wiki';
        }

        if ($activeLocationFilter !== null) {
            return 'Missions at '.$activeLocationFilter['name'].' - Star Citizen Wiki';
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
