<?php

declare(strict_types=1);

namespace App\Support\Seo;

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

    protected function metaDescription(array $data): string
    {
        return 'Browse all Star Citizen blueprints. Filter by output type, class, craft time, and ingredients to find crafting recipes.';
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $data): array
    {
        return ['Star Citizen', 'SC', 'blueprints', 'crafting', 'recipes'];
    }

    protected function ogTitle(string $pageTitle, array $data): string
    {
        return $this->itemListName();
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    protected function breadcrumbs(string $canonicalUrl, array $versionParams, array $data): array
    {
        return [
            ['label' => 'Blueprints', 'url' => $canonicalUrl],
        ];
    }
}
