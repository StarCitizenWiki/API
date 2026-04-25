<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Carbon\CarbonInterval;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class MissionShowSeoData extends AbstractShowSeoData
{
    protected function showRouteName(): string
    {
        return 'web.missions.show';
    }

    protected function showRouteParameterName(): string
    {
        return 'mission';
    }

    /**
     * @return array<string, mixed>
     */
    public function build(array $mission, Request $request): array
    {
        $title = data_get($mission, 'title') ?? 'Mission';
        $type = data_get($mission, 'mission_type');
        $factionName = data_get($mission, 'faction.name');
        $missionGiver = data_get($mission, 'mission_giver');
        $legalityLabel = data_get($mission, 'legality_label');
        $uuid = data_get($mission, 'uuid');
        $description = $this->resolveDescription(data_get($mission, 'description'));
        $version = $this->resolveVersionCode($request);
        $canonicalUrl = data_get($mission, 'web_url')
            ?? $this->fallbackShowUrl($uuid, $version);

        $breadcrumbs = $this->buildBreadcrumbs($title, $factionName, $canonicalUrl, $version);
        $metaTitle = $this->pipeTitle([$title, $type ?? 'Mission'], 'Star Citizen Mission');
        $metaDescription = Str::limit(
            $description ?? $this->buildFallbackDescription($title, $type, $factionName, $mission),
            160,
        );

        $missionSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'Action',
            'name' => $title,
            'description' => $metaDescription,
            'url' => $canonicalUrl,
        ];

        if ($factionName !== null) {
            $missionSchema['agent'] = [
                '@type' => 'Organization',
                'name' => $factionName,
            ];
        }

        $missionAdditionalProperty = $this->buildPropertyValues([
            'Type' => $type,
            'Faction' => $factionName,
            'Reputation XP' => data_get($mission, 'reputation_amount'),
            'Legality' => data_get($mission, 'legality_label'),
            'Has Combat' => data_get($mission, 'has_combat') === true ? 'Yes' : null,
            'Enemy Count' => $this->buildEnemyCountRange($mission),
            'Rank' => data_get($mission, 'rank_index'),
            'Time to Complete' => ($mins = data_get($mission, 'time_to_complete_minutes')) !== null
                ? CarbonInterval::minutes((int) $mins)->cascade()->forHumans(short: true)
                : null,
            'Version' => data_get($mission, 'game_version'),
        ]);

        if ($missionAdditionalProperty !== []) {
            $missionSchema['additionalProperty'] = $missionAdditionalProperty;
        }

        return $this->buildSeoResponse(
            canonicalUrl: $canonicalUrl,
            metaDescription: $metaDescription,
            keywords: $this->keywords([
                $title,
                $type,
                $factionName,
                $missionGiver,
                $legalityLabel,
            ]),
            ogTitle: $metaTitle,
            breadcrumbs: $breadcrumbs,
            structuredData: [
                $this->buildBreadcrumbStructuredData($breadcrumbs),
                $missionSchema,
            ],
            title: $metaTitle,
        );
    }

    /**
     * @return array<int, array{label: string, url: string}>
     */
    private function buildBreadcrumbs(string $title, ?string $factionName, string $canonicalUrl, ?string $version): array
    {
        $versionParams = $version !== null ? ['version' => $version] : [];

        $breadcrumbs = [[
            'label' => 'All Missions',
            'url' => route('web.missions.index', $versionParams),
        ]];

        if ($factionName !== null) {
            $breadcrumbs[] = [
                'label' => $factionName,
                'url' => route('web.missions.index', array_merge($versionParams, [
                    'filter' => ['faction' => $factionName],
                ])),
            ];
        }

        $breadcrumbs[] = [
            'label' => $title,
            'url' => $canonicalUrl,
        ];

        return $breadcrumbs;
    }

    private function buildFallbackDescription(string $title, ?string $type, ?string $factionName, array $mission): string
    {
        $typeLabel = $type ?? 'mission';
        $base = 'Browse Star Citizen '.$typeLabel.' mission data for '.$title;

        if ($factionName !== null) {
            $base .= ' from '.$factionName;
        }

        $reputationAmount = data_get($mission, 'reputation_amount');
        if ($reputationAmount !== null) {
            $base .= '. '.number_format($reputationAmount).' reputation XP';
        }

        $base .= '. View rewards, locations, and technical details.';

        return $base;
    }

    private function buildEnemyCountRange(array $mission): ?string
    {
        $min = data_get($mission, 'enemy_count_min');
        $max = data_get($mission, 'enemy_count_max');

        if ($min === null && $max === null) {
            return null;
        }

        if ($min !== null && $max !== null && $min === $max) {
            return (string) $min;
        }

        return ($min ?? 0).'-'.($max ?? '?');
    }
}
