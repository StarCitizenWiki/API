<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;
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

    /**
     * @param  array{pageTitle?: string, category?: string|null, type?: string|null}  $data
     * @return array<string, mixed>
     */
    public function build(array $data, Request $request): array
    {
        $versionCode = $this->resolveVersionCode($request);
        $versionParams = $versionCode !== null ? ['version' => $versionCode] : [];
        $canonicalUrl = route($this->indexRouteName(), $versionParams);

        $pageTitle = $this->normalizeString($data['pageTitle'] ?? null) ?? 'Star Citizen Items';
        $category = $this->normalizeString($data['category'] ?? null);
        $type = $this->normalizeString($data['type'] ?? null);

        $metaDescription = $this->buildMetaDescription($category, $type);
        $keywords = $this->buildKeywords($category, $type);
        $ogTitle = $this->buildOgTitle($pageTitle);
        $breadcrumbs = $this->buildBreadcrumbs($versionParams, $category, $type);
        $structuredData = $this->buildIndexStructuredData($pageTitle, $metaDescription, $canonicalUrl, $breadcrumbs);

        return $this->buildSeoResponse($canonicalUrl, $metaDescription, $keywords, $ogTitle, $breadcrumbs, $structuredData);
    }

    private function buildMetaDescription(?string $category, ?string $type): string
    {
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
    private function buildKeywords(?string $category, ?string $type): array
    {
        $keywords = ['Star Citizen', 'SC', 'items', 'item database'];

        if ($type !== null) {
            $keywords[] = Str::headline($type);
            $keywords[] = Str::headline($type).' items';
        }

        if ($category !== null) {
            $keywords[] = Str::headline($category);
        }

        return $keywords;
    }

    private function buildOgTitle(string $pageTitle): string
    {
        return $pageTitle.' - Star Citizen Items';
    }

    /**
     * @param  array<string, string>  $versionParams
     * @return array<int, array{label: string, url: string|null}>
     */
    private function buildBreadcrumbs(array $versionParams, ?string $category, ?string $type): array
    {
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

    protected function fallbackShowUrl(?string $uuid, ?string $version): string
    {
        return route($this->indexRouteName(), array_filter([
            'version' => $version,
        ]));
    }
}
