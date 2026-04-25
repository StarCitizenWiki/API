<?php

declare(strict_types=1);

namespace App\Support\Seo;

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
            $count = is_numeric($total) ? number_format((int) $total).' ' : '';

            $parts = ["Browse {$count}Star Citizen {$label} items"];

            if ($manufacturer !== null) {
                $parts[] = 'from '.$manufacturer;
            }

            $parts[] = 'Filter by grade, size, and manufacturer to find the right '.$label.' for your ship or loadout.';

            return Str::limit(implode('. ', $parts).'.', 160);
        }

        if ($category !== null) {
            $label = Str::headline($category);
            $count = is_numeric($total) ? number_format((int) $total).' ' : '';

            $parts = ["Browse {$count}Star Citizen {$label}"];

            if ($manufacturer !== null) {
                $parts[] = 'from '.$manufacturer;
            }

            $parts[] = 'weapons, armor, gadgets, components, and more. Filter by type, grade, and size.';

            return Str::limit(implode('. ', $parts).'.', 160);
        }

        $count = is_numeric($total) ? number_format((int) $total).' ' : '';

        return Str::limit("Browse the complete {$count}Star Citizen items database - weapons, armor, gadgets, components, and more. Filter by type, grade, and size.", 160);
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $entityFields): array
    {
        $keywords = ['Star Citizen', 'SC', 'items', 'item database'];
        $category = $entityFields['category'] ?? null;
        $type = $entityFields['type'] ?? null;
        $manufacturer = Arr::get($entityFields, 'manufacturer');

        if ($type !== null) {
            $keywords[] = Str::headline($type);
            $keywords[] = Str::headline($type).' items';
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
            return $manufacturer.' '.Str::headline($type).' - Star Citizen Items';
        }

        if ($manufacturer !== null) {
            return $manufacturer.' Items - Star Citizen Items';
        }

        return $pageTitle.' - Star Citizen Items';
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
