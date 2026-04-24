<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;

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

    /**
     * @param  array{activeLocationFilter?: array{name: string, url: string}|null, pageTitle?: string}  $data
     * @return array<string, mixed>
     */
    public function build(array $data, Request $request): array
    {
        $versionCode = $this->resolveVersionCode($request);
        $versionParams = $versionCode !== null ? ['version' => $versionCode] : [];
        $canonicalUrl = route($this->indexRouteName(), $versionParams);

        $activeLocationFilter = $data['activeLocationFilter'] ?? null;
        $pageTitle = $this->normalizeString($data['pageTitle'] ?? null) ?? 'Star Citizen Missions';

        $metaDescription = $this->buildMetaDescription($activeLocationFilter);
        $keywords = $this->buildKeywords($activeLocationFilter);
        $ogTitle = $this->buildOgTitle($activeLocationFilter);
        $breadcrumbs = $this->buildBreadcrumbs($canonicalUrl, $activeLocationFilter);
        $structuredData = $this->buildIndexStructuredData($pageTitle, $metaDescription, $canonicalUrl, $breadcrumbs);

        return $this->buildSeoResponse($canonicalUrl, $metaDescription, $keywords, $ogTitle, $breadcrumbs, $structuredData);
    }

    /**
     * @param  array{name: string, url: string}|null  $activeLocationFilter
     */
    private function buildMetaDescription(?array $activeLocationFilter): string
    {
        if ($activeLocationFilter !== null) {
            $name = $activeLocationFilter['name'];

            return "Browse Star Citizen missions available at {$name}. Filter by faction, type, legality, and more to find your next objective.";
        }

        return 'Browse all Star Citizen missions. Filter by faction, type, location, legality, and more to find your next objective.';
    }

    /**
     * @param  array{name: string, url: string}|null  $activeLocationFilter
     * @return array<int, string>
     */
    private function buildKeywords(?array $activeLocationFilter): array
    {
        $keywords = ['Star Citizen', 'SC', 'missions', 'mission guide'];

        if ($activeLocationFilter !== null) {
            $keywords[] = $activeLocationFilter['name'];
            $keywords[] = 'missions at '.$activeLocationFilter['name'];
        }

        return $keywords;
    }

    /**
     * @param  array{name: string, url: string}|null  $activeLocationFilter
     */
    private function buildOgTitle(?array $activeLocationFilter): string
    {
        if ($activeLocationFilter !== null) {
            return 'Star Citizen Missions at '.$activeLocationFilter['name'];
        }

        return 'Star Citizen Missions';
    }

    /**
     * @param  array{name: string, url: string}|null  $activeLocationFilter
     * @return array<int, array{label: string, url: string|null}>
     */
    private function buildBreadcrumbs(string $canonicalUrl, ?array $activeLocationFilter): array
    {
        $breadcrumbs = [
            ['label' => 'Missions', 'url' => $canonicalUrl],
        ];

        if ($activeLocationFilter !== null) {
            $breadcrumbs[0]['url'] = $canonicalUrl;
            $breadcrumbs[] = ['label' => $activeLocationFilter['name'], 'url' => null];
        }

        return $breadcrumbs;
    }

    protected function fallbackShowUrl(?string $uuid, ?string $version): string
    {
        return route($this->indexRouteName(), array_filter([
            'version' => $version,
        ]));
    }
}
