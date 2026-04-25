<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Support\Str;

class ItemIndexSeoData extends AbstractIndexSeoData
{
    protected function indexRouteName(): string
    {
        return 'web.items.index';
    }

    protected function itemListName(): string
    {
        return 'Star Citizen Items';
    }

    protected function metaDescription(array $data): string
    {
        $category = $data['category'] ?? null;
        $type = $data['type'] ?? null;

        if ($type !== null) {
            $label = Str::headline($type);

            return "Browse Star Citizen {$label} items. Filter by grade, size, and manufacturer to find the right {$label} for your ship or loadout.";
        }

        if ($category !== null) {
            $label = Str::headline($category);

            return "Browse Star Citizen {$label} - weapons, armor, gadgets, components, and more. Filter by type, grade, and size.";
        }

        return 'Browse the complete Star Citizen items database - weapons, armor, gadgets, components, and more. Filter by type, grade, and size.';
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $data): array
    {
        $keywords = ['Star Citizen', 'SC', 'items', 'item database'];
        $category = $data['category'] ?? null;
        $type = $data['type'] ?? null;

        if ($type !== null) {
            $keywords[] = Str::headline($type);
            $keywords[] = Str::headline($type).' items';
        }

        if ($category !== null) {
            $keywords[] = Str::headline($category);
        }

        return $keywords;
    }

    protected function ogTitle(string $pageTitle, array $data): string
    {
        return $pageTitle.' - Star Citizen Items';
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    protected function breadcrumbs(string $canonicalUrl, array $versionParams, array $data): array
    {
        $category = $data['category'] ?? null;
        $type = $data['type'] ?? null;

        $breadcrumbs = [
            ['label' => 'All Items', 'url' => route('web.items.index', $versionParams)],
        ];

        if ($category !== null) {
            $breadcrumbs[] = [
                'label' => Str::headline($category),
                'url' => route('web.items.index', array_merge($versionParams, [
                    'filter' => ['category' => $category],
                ])),
            ];
        }

        if ($type !== null) {
            $filter = [];
            if ($category !== null) {
                $filter['category'] = $category;
            }
            $filter['type'] = $type;

            $breadcrumbs[] = [
                'label' => Str::headline($type),
                'url' => route('web.items.index', array_merge($versionParams, [
                    'filter' => $filter,
                ])),
            ];
        }

        return $breadcrumbs;
    }
}
