<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

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
        $total = Arr::get($data, 'total');
        $outputType = Arr::get($data, 'output_type');

        if ($outputType !== null && is_numeric($total)) {
            return Str::limit(
                collect([
                    'Browse '.number_format((int) $total).' Star Citizen blueprints',
                    'filtered by type '.$outputType,
                    'Filter by output type, class, craft time, and ingredients to find crafting recipes.',
                ])->filter()->implode('. ').'.',
                160,
            );
        }

        if (is_numeric($total)) {
            return Str::limit(
                'Browse '.number_format((int) $total).' Star Citizen blueprints. Filter by output type, class, craft time, and ingredients to find crafting recipes.',
                160,
            );
        }

        return 'Browse all Star Citizen blueprints. Filter by output type, class, craft time, and ingredients to find crafting recipes.';
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $entityFields): array
    {
        $base = ['Star Citizen', 'SC', 'blueprints', 'crafting', 'recipes'];

        $outputType = Arr::get($entityFields, 'output_type');
        if ($outputType !== null) {
            $base[] = $outputType;
        }

        $outputClass = Arr::get($entityFields, 'output_class');
        if ($outputClass !== null) {
            $base[] = $outputClass;
        }

        return array_values(array_unique(array_filter($base)));
    }

    protected function ogTitle(string $pageTitle, array $data): string
    {
        $outputType = Arr::get($data, 'output_type');

        if ($outputType !== null) {
            return $outputType.' Blueprints - Star Citizen';
        }

        return $this->itemListName();
    }

    /**
     * @return array<int, array{label: string, url: string|null}>
     */
    protected function breadcrumbs(string $canonicalUrl, array $versionParams, array $data): array
    {
        $outputType = Arr::get($data, 'output_type');

        if ($outputType !== null) {
            return [
                ['label' => 'Blueprints', 'url' => route($this->indexRouteName(), $versionParams)],
                ['label' => $outputType, 'url' => $canonicalUrl],
            ];
        }

        return [
            ['label' => 'Blueprints', 'url' => $canonicalUrl],
        ];
    }
}
