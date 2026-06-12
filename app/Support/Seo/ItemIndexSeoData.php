<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Support\Format;
use Illuminate\Support\Arr;
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
        $total = Arr::get($data, 'total');
        $manufacturer = Arr::get($data, 'manufacturer');

        if ($type !== null) {
            $label = Str::headline($type);
            $count = is_numeric($total) ? Format::number((int) $total).' ' : '';

            if ($manufacturer !== null) {
                return "{$count}{$manufacturer} {$label}. Compare grades, sizes, and stats for the full {$manufacturer} lineup.";
            }

            return "{$count}{$label} components and items. Compare grades, sizes, and manufacturers to find the best fit.";
        }

        if ($category !== null) {
            $label = Str::headline($category);
            $count = is_numeric($total) ? Format::number((int) $total).' ' : '';

            if ($manufacturer !== null) {
                return "{$count}{$manufacturer} {$label}. Stats, grades, and variants for every {$manufacturer} item in this category.";
            }

            return "{$count}{$label}. Weapons, armor, gadgets, and equipment with detailed stats and grades.";
        }

        $count = is_numeric($total) ? Format::number((int) $total).' ' : '';

        return "{$count}items in all categories. Weapons, armor, components, gadgets, and more.";
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $entityFields): array
    {
        $keywords = ['Star Citizen', 'items', 'components', 'equipment', 'item stats'];
        $category = $entityFields['category'] ?? null;
        $type = $entityFields['type'] ?? null;
        $manufacturer = Arr::get($entityFields, 'manufacturer');

        if ($type !== null) {
            $keywords[] = Str::headline($type);
        }

        if ($category !== null) {
            $keywords[] = Str::headline($category);
        }

        if ($manufacturer !== null) {
            $keywords[] = $manufacturer;
        }

        return array_values(array_unique(array_filter($keywords)));
    }

    protected function ogTitle(string $pageTitle, array $data): string
    {
        $manufacturer = Arr::get($data, 'manufacturer');
        $type = $data['type'] ?? null;

        if ($manufacturer !== null && $type !== null) {
            return $manufacturer.' '.Str::headline($type).' - Star Citizen Wiki';
        }

        if ($manufacturer !== null) {
            return $manufacturer.' Items - Star Citizen Wiki';
        }

        return 'Star Citizen Items & Components';
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    protected function breadcrumbs(string $canonicalUrl, array $versionParams, array $data): array
    {
        $category = $data['category'] ?? null;
        $type = $data['type'] ?? null;
        $manufacturer = Arr::get($data, 'manufacturer');

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

        if ($manufacturer !== null) {
            $breadcrumbs[] = [
                'label' => $manufacturer,
                'url' => null,
            ];
        }

        return $breadcrumbs;
    }
}
