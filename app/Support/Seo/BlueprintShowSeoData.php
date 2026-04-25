<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Models\Game\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class BlueprintShowSeoData extends AbstractShowSeoData
{
    protected function showRouteName(): string
    {
        return 'web.blueprints.show';
    }

    protected function showRouteParameterName(): string
    {
        return 'blueprint';
    }

    /**
     * @return array<string, mixed>
     */
    public function build(array $blueprint, Request $request, bool $isEmptyMode = false): array
    {
        if ($isEmptyMode) {
            return $this->buildEmptySeo($request);
        }

        $blueprintName = data_get($blueprint, 'output_name')
            ?? data_get($blueprint, 'output.name')
            ?? 'Blueprint';
        $outputClass = data_get($blueprint, 'output_class')
            ?? data_get($blueprint, 'output.class');
        $outputType = data_get($blueprint, 'output.type');
        $uuid = data_get($blueprint, 'uuid');
        $craftTimeSeconds = data_get($blueprint, 'craft_time_seconds');
        $ingredientCount = (int) data_get($blueprint, 'ingredient_count', 0);
        $version = $this->resolveVersionCode($request);
        $canonicalUrl = data_get($blueprint, 'web_url')
            ?? $this->fallbackShowUrl($uuid, $version);
        $breadcrumbs = $this->buildBreadcrumbs($blueprintName, $canonicalUrl, $version);

        $metaTitle = $blueprintName.' Blueprint';
        $metaDescription = Str::limit(
            trim(collect([
                $blueprintName.' blueprint',
                $outputType !== null ? 'type '.$outputType : null,
                $craftTimeSeconds !== null ? 'craft time '.$craftTimeSeconds.' seconds' : null,
                $ingredientCount > 0 ? $ingredientCount.' inputs' : null,
            ])->filter()->implode(', ')),
            160,
        );

        $ingredients = $this->resolveIngredients($blueprint);

        return $this->buildSeoResponse(
            canonicalUrl: $canonicalUrl,
            metaDescription: $metaDescription,
            keywords: $this->keywords([
                $blueprintName,
                $outputType,
                $outputClass,
                'Blueprint',
            ]),
            ogTitle: $metaTitle,
            breadcrumbs: $breadcrumbs,
            structuredData: [
                $this->buildBreadcrumbStructuredData($breadcrumbs),
                $this->buildHowToStructuredData(
                    blueprintName: $blueprintName,
                    metaDescription: $metaDescription,
                    canonicalUrl: $canonicalUrl,
                    craftTimeSeconds: $craftTimeSeconds,
                    ingredients: $ingredients,
                    outputType: $outputType,
                    outputClass: $outputClass,
                    uuid: $uuid,
                ),
            ],
            title: $metaTitle,
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function buildEmptySeo(Request $request): array
    {
        $version = $this->resolveVersionCode($request);
        $canonicalUrl = route('web.blueprints.search', array_filter([
            'version' => $version,
        ]));
        $metaTitle = 'Search Blueprints - Star Citizen';
        $metaDescription = 'Search Star Citizen blueprints by output name, class, item, or input resource.';

        return [
            'title' => $metaTitle,
            'metaDescription' => $metaDescription,
            'canonicalUrl' => $canonicalUrl,
            'keywords' => [],
            'ogTitle' => $metaTitle,
            'ogDescription' => $metaDescription,
            'twitterTitle' => $metaTitle,
            'twitterDescription' => $metaDescription,
            'breadcrumbs' => [],
            'structuredData' => [],
            'robots' => 'noindex,follow',
        ];
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function buildBreadcrumbs(string $blueprintName, string $canonicalUrl, ?string $version): array
    {
        $versionParams = $version !== null ? ['version' => $version] : [];

        return [
            [
                'label' => 'All Blueprints',
                'url' => route('web.blueprints.index', $versionParams),
            ],
            [
                'label' => $blueprintName,
                'url' => $canonicalUrl,
            ],
        ];
    }

    /**
     * @param  array<int, array{name: string}>  $ingredients
     * @return array<string, mixed>
     */
    private function buildHowToStructuredData(
        string $blueprintName,
        string $metaDescription,
        string $canonicalUrl,
        mixed $craftTimeSeconds,
        array $ingredients,
        ?string $outputType,
        ?string $outputClass,
        ?string $uuid,
    ): array {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'HowTo',
            'name' => $blueprintName.' Blueprint',
            'description' => $metaDescription,
            'url' => $canonicalUrl,
        ];

        if ($uuid !== null) {
            $schema['identifier'] = $uuid;
        }

        if (is_numeric($craftTimeSeconds)) {
            $schema['totalTime'] = 'PT'.((int) $craftTimeSeconds).'S';
        }

        $supply = [];
        foreach ($ingredients as $ingredient) {
            $name = data_get($ingredient, 'name');
            if ($name !== null) {
                $supply[] = [
                    '@type' => 'HowToSupply',
                    'name' => $name,
                ];
            }
        }

        if ($supply !== []) {
            $schema['supply'] = $supply;
        }

        $stepText = collect([
            'Combine ingredients to craft '.$blueprintName.'.',
        ])->filter()->implode(' ');

        $schema['step'] = [
            '@type' => 'HowToStep',
            'name' => 'Craft '.$blueprintName,
            'text' => $stepText,
        ];

        $additionalProperty = $this->buildPropertyValues([
            'Output Type' => $outputType,
            'Output Class' => $outputClass,
        ]);

        if ($additionalProperty !== []) {
            $schema['additionalProperty'] = $additionalProperty;
        }

        return $schema;
    }

    /**
     * @return array<int, array{name: string}>
     */
    private function resolveIngredients(array $blueprint): array
    {
        $raw = data_get($blueprint, 'ingredients');

        if (! is_array($raw)) {
            return [];
        }

        $ingredients = [];
        foreach ($raw as $ingredient) {
            if (! is_array($ingredient)) {
                continue;
            }

            $name = data_get($ingredient, 'name');
            if ($name !== null) {
                $ingredients[] = ['name' => $name];
            }
        }

        return $ingredients;
    }

    protected function fallbackShowUrl(?string $uuid, ?string $version): string
    {
        if ($uuid === null) {
            return url()->current();
        }

        $slug = Blueprint::query()->where('uuid', $uuid)->value('slug');

        return route($this->showRouteName(), array_filter([
            $this->showRouteParameterName() => $slug ?? $uuid,
            'version' => $version,
        ]));
    }
}
