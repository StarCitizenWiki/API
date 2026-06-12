<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;

abstract class AbstractSeoData
{
    /**
     * @return array<int, string>
     */
    protected function baseKeywords(): array
    {
        return ['Star Citizen', 'SC'];
    }

    /**
     * @param  array<int, array{label: string, url: string}>  $breadcrumbs
     * @return array<string, mixed>|null
     */
    protected function buildBreadcrumbStructuredData(array $breadcrumbs): ?array
    {
        $items = [];

        foreach ($breadcrumbs as $breadcrumb) {
            $name = $breadcrumb['label'] ?? null;
            $url = $breadcrumb['url'] ?? null;

            if ($name === null || $url === null) {
                continue;
            }

            $items[] = [
                '@type' => 'ListItem',
                'position' => count($items) + 1,
                'name' => $name,
                'item' => $url,
            ];
        }

        if ($items === []) {
            return null;
        }

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    protected function resolveVersionCode(Request $request): ?string
    {
        $rawQueryVersion = $request->query('version');
        $queryVersion = is_string($rawQueryVersion) && trim($rawQueryVersion) !== '' ? trim($rawQueryVersion) : null;

        if ($queryVersion !== null) {
            return $queryVersion;
        }

        if (! $request->hasSession()) {
            return null;
        }

        return $request->session()->get('game_version_code');
    }

    /**
     * @param  array<int, mixed>  $entityFields
     * @return array<int, mixed>
     */
    protected function keywords(array $entityFields): array
    {
        return array_values(array_filter(
            [...$entityFields, ...$this->baseKeywords()],
            static fn (mixed $value): bool => $value !== null && $value !== '',
        ));
    }

    /**
     * @param  array<int, mixed>  $segments
     */
    protected function pipeTitle(array $segments, string $suffix = 'Star Citizen'): string
    {
        return array_filter(
            [...$segments, $suffix],
            static fn (mixed $value): bool => $value !== null && $value !== '',
        )
                |> array_values(...)
                |> (static fn ($x) => implode(' - ', $x));
    }

    /**
     * @param  array<int, mixed>  $segments
     */
    protected function joinSegments(array $segments, string $glue = ' '): string
    {
        return array_filter(
            $segments,
            static fn (mixed $value): bool => $value !== null && $value !== '',
        )
                |> array_values(...)
                |> (static fn ($x) => implode($glue, $x));
    }

    /**
     * @param  array<int, string>  $keywords
     * @param  array<int, array<string, mixed>>  $structuredData
     * @return array<string, mixed>
     */
    protected function buildSeoResponse(
        string $canonicalUrl,
        string $metaDescription,
        array $keywords,
        string $ogTitle,
        array $breadcrumbs,
        array $structuredData,
        ?string $title = null,
    ): array {
        $response = [];

        if ($title !== null) {
            $response['title'] = $title;
        }

        $response['canonicalUrl'] = $canonicalUrl;
        $response['metaDescription'] = $metaDescription;
        $response['keywords'] = $keywords;
        $response['ogTitle'] = $ogTitle;
        $response['ogDescription'] = $metaDescription;
        $response['twitterTitle'] = $ogTitle;
        $response['twitterDescription'] = $metaDescription;
        $response['breadcrumbs'] = $breadcrumbs;
        $response['structuredData'] = array_values(array_filter(
            $structuredData,
            static fn (mixed $value): bool => $value !== null && $value !== '',
        ));

        return $response;
    }
}
