<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;

class BlueprintIndexSeoData extends AbstractIndexSeoData
{
    protected function indexRouteName(): string
    {
        return 'web.blueprints.index';
    }

    protected function itemListName(): string
    {
        return 'Star Citizen Blueprints';
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

        $pageTitle = $this->normalizeString($data['pageTitle'] ?? null) ?? 'Star Citizen Blueprints';
        $metaDescription = $this->buildMetaDescription();
        $keywords = $this->buildKeywords();
        $ogTitle = $this->itemListName();
        $breadcrumbs = $this->buildBreadcrumbs($canonicalUrl);
        $structuredData = $this->buildIndexStructuredData($pageTitle, $metaDescription, $canonicalUrl, $breadcrumbs);

        return $this->buildSeoResponse($canonicalUrl, $metaDescription, $keywords, $ogTitle, $breadcrumbs, $structuredData);
    }

    private function buildMetaDescription(): string
    {
        return 'Browse all Star Citizen blueprints. Filter by output type, class, craft time, and ingredients to find crafting recipes.';
    }

    /**
     * @return array<int, string>
     */
    private function buildKeywords(): array
    {
        return ['Star Citizen', 'SC', 'blueprints', 'crafting', 'recipes'];
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function buildBreadcrumbs(string $canonicalUrl): array
    {
        return [
            ['label' => 'Blueprints', 'url' => $canonicalUrl],
        ];
    }

    protected function fallbackShowUrl(?string $uuid, ?string $version): string
    {
        return route($this->indexRouteName(), array_filter([
            'version' => $version,
        ]));
    }
}
