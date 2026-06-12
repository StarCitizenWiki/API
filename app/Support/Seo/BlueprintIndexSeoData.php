<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Support\Format;
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
                Format::number((int) $total).' '.$outputType.' crafting blueprints with ingredient lists, craft times, and output details.',
                160,
            );
        }

        if (is_numeric($total)) {
            return Str::limit(
                Format::number((int) $total).' crafting blueprints. Find recipes by output type, craft time, and required ingredients.',
                160,
            );
        }

        return 'Crafting blueprints with ingredient lists, craft times, and output details. Search by item name or resource.';
    }

    /**
     * @return array<int, string>
     */
    protected function keywords(array $entityFields): array
    {
        $base = ['Star Citizen', 'blueprints', 'crafting', 'recipes', 'ingredients'];

        $outputType = Arr::get($entityFields, 'output_type');
        if ($outputType !== null) {
            $base[] = $outputType;
            $base[] = $outputType.' blueprint';
        }

        $outputClass = Arr::get($entityFields, 'output_class');
        if ($outputClass !== null) {
            $base[] = $outputClass;
        }

        return $base
                |> array_filter(...)
                |> array_unique(...)
                |> array_values(...);
    }

    protected function ogTitle(string $pageTitle, array $data): string
    {
        $outputType = Arr::get($data, 'output_type');

        if ($outputType !== null) {
            return $outputType.' Blueprints - Star Citizen Wiki';
        }

        return 'Star Citizen Crafting Blueprints';
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
