<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;

abstract class AbstractIndexSeoData extends AbstractSeoData
{
    abstract protected function indexRouteName(): string;

    abstract protected function itemListName(): string;

    protected function defaultPageTitle(): string
    {
        return $this->itemListName();
    }

    abstract protected function metaDescription(array $data): string;

    /**
     * @return array<int, string>
     */
    abstract protected function keywords(array $data): array;

    protected function ogTitle(string $pageTitle, array $data): string
    {
        return $pageTitle;
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    abstract protected function breadcrumbs(string $canonicalUrl, array $versionParams, array $data): array;

    /**
     * @param  array<int, array{label: string, url: string|null}>  $breadcrumbs
     * @return array<int, array<string, mixed>>
     */
    protected function buildIndexStructuredData(string $pageTitle, string $metaDescription, string $canonicalUrl, array $breadcrumbs): array
    {
        $collectionPage = [
            '@context' => 'https://schema.org',
            '@type' => 'CollectionPage',
            'name' => $pageTitle,
            'description' => $metaDescription,
            'url' => $canonicalUrl,
            'mainEntity' => [
                '@type' => 'ItemList',
                'name' => $this->itemListName(),
            ],
        ];

        $breadcrumbSchema = $this->buildBreadcrumbStructuredData($breadcrumbs);

        return array_values(array_filter([
            $collectionPage,
            $breadcrumbSchema,
        ]));
    }

    /**
     * @return array<string, mixed>
     */
    public function build(array $data, Request $request): array
    {
        $versionCode = $this->resolveVersionCode($request);
        $versionParams = $versionCode !== null ? ['version' => $versionCode] : [];
        $canonicalUrl = route($this->indexRouteName(), $versionParams);

        $pageTitle = $data['pageTitle'] ?? $this->defaultPageTitle();
        $metaDescription = $this->metaDescription($data);
        $keywords = $this->keywords($data);
        $ogTitle = $this->ogTitle($pageTitle, $data);
        $breadcrumbs = $this->breadcrumbs($canonicalUrl, $versionParams, $data);
        $structuredData = $this->buildIndexStructuredData($pageTitle, $metaDescription, $canonicalUrl, $breadcrumbs);

        return $this->buildSeoResponse($canonicalUrl, $metaDescription, $keywords, $ogTitle, $breadcrumbs, $structuredData);
    }
}
