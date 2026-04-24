<?php

declare(strict_types=1);

namespace App\Support\Seo;

abstract class AbstractIndexSeoData extends AbstractShowSeoData
{
    abstract protected function indexRouteName(): string;

    abstract protected function itemListName(): string;

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
}
