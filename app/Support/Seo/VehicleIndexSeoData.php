<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;

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

    /**
     * @param  array{pageTitle?: string}  $data
     * @return array<string, mixed>
     */
    public function build(array $data, Request $request): array
    {
        $versionCode = $this->resolveVersionCode($request);
        $versionParams = $versionCode !== null ? ['version' => $versionCode] : [];
        $canonicalUrl = route($this->indexRouteName(), $versionParams);

        $pageTitle = $this->normalizeString($data['pageTitle'] ?? null) ?? 'Star Citizen Vehicles';
        $metaDescription = $this->buildMetaDescription();
        $keywords = $this->buildKeywords();
        $ogTitle = $this->buildOgTitle($pageTitle);
        $breadcrumbs = $this->buildBreadcrumbs($canonicalUrl);
        $structuredData = $this->buildIndexStructuredData($pageTitle, $metaDescription, $canonicalUrl, $breadcrumbs);

        return $this->buildSeoResponse($canonicalUrl, $metaDescription, $keywords, $ogTitle, $breadcrumbs, $structuredData);
    }

    private function buildMetaDescription(): string
    {
        return 'Explore all Star Citizen vehicles including ships, ground vehicles, and gravlevs. Filter by manufacturer, career, role, and size.';
    }

    /**
     * @return array<int, string>
     */
    private function buildKeywords(): array
    {
        return ['Star Citizen', 'SC', 'vehicles', 'ships', 'ground vehicles'];
    }

    private function buildOgTitle(string $pageTitle): string
    {
        return $pageTitle.' - Star Citizen Vehicles';
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function buildBreadcrumbs(string $canonicalUrl): array
    {
        return [
            ['label' => 'Vehicles', 'url' => $canonicalUrl],
        ];
    }

    protected function fallbackShowUrl(?string $uuid, ?string $version): string
    {
        return route($this->indexRouteName(), array_filter([
            'version' => $version,
        ]));
    }
}
