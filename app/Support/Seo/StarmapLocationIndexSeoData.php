<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;

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

    /**
     * @param  array{pageTitle?: string}  $data
     * @return array<string, mixed>
     */
    public function build(array $data, Request $request): array
    {
        $versionCode = $this->resolveVersionCode($request);
        $versionParams = $versionCode !== null ? ['version' => $versionCode] : [];
        $canonicalUrl = route($this->indexRouteName(), $versionParams);

        $pageTitle = $this->normalizeString($data['pageTitle'] ?? null) ?? 'Star Citizen Starmap Locations';
        $metaDescription = $this->buildMetaDescription();
        $keywords = $this->buildKeywords();
        $ogTitle = $this->buildOgTitle($pageTitle);
        $breadcrumbs = $this->buildBreadcrumbs($canonicalUrl);
        $structuredData = $this->buildIndexStructuredData($pageTitle, $metaDescription, $canonicalUrl, $breadcrumbs);

        return $this->buildSeoResponse($canonicalUrl, $metaDescription, $keywords, $ogTitle, $breadcrumbs, $structuredData);
    }

    private function buildMetaDescription(): string
    {
        return 'Browse all Star Citizen starmap locations including planets, stations, outposts, and landing zones. Filter by system, type, amenities, and more.';
    }

    /**
     * @return array<int, string>
     */
    private function buildKeywords(): array
    {
        return ['Star Citizen', 'SC', 'starmap', 'locations', 'planets', 'stations', 'outposts'];
    }

    private function buildOgTitle(string $pageTitle): string
    {
        return $pageTitle.' - Star Citizen Starmap';
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function buildBreadcrumbs(string $canonicalUrl): array
    {
        return [
            ['label' => 'Starmap Locations', 'url' => $canonicalUrl],
        ];
    }

    protected function fallbackShowUrl(?string $uuid, ?string $version): string
    {
        return route($this->indexRouteName(), array_filter([
            'version' => $version,
        ]));
    }
}
