<?php

declare(strict_types=1);

namespace App\Support\Seo;

use App\Support\Format;
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

        if ($uuid !== null) {
            $missionSchema['identifier'] = $uuid;
        }

        if ($factionName !== null) {
            $missionSchema['agent'] = [
                '@type' => 'Organization',
                'name' => $factionName,
            ];
        }

        $location = $this->buildLocation($mission);
        if ($location !== null) {
            $missionSchema['location'] = $location;
        }

        $object = $this->buildRewardOffer($mission);
        if ($object !== null) {
            $missionSchema['object'] = $object;
        }

        $instrument = $this->buildBlueprintInstrument($mission);
        if ($instrument !== null) {
            $missionSchema['instrument'] = $instrument;
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
            'Reward Scope' => data_get($mission, 'reward_scope'),
            'Shareable' => data_get($mission, 'shareable') === true ? 'Yes' : null,
            'Once Only' => data_get($mission, 'once_only') === true ? 'Yes' : null,
            'Available in Prison' => data_get($mission, 'available_in_prison') === true ? 'Yes' : null,
            'Has Defend Objective' => data_get($mission, 'has_defend_objective') === true ? 'Yes' : null,
            'Crime Stat Range' => $this->buildCrimeStatRange($mission),
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
                data_get($mission, 'reward_scope'),
                $this->firstStarSystem($mission),
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
        $segments = ['Browse Star Citizen '.$typeLabel.' mission data for '.$title];

        if ($factionName !== null) {
            $segments[] = 'from '.$factionName;
        }

        $rewardSegment = $this->buildRewardSegment($mission);
        if ($rewardSegment !== null) {
            $segments[] = $rewardSegment;
        }

        $legalityLabel = data_get($mission, 'legality_label');
        if ($legalityLabel !== null) {
            $segments[] = $legalityLabel;
        }

        $reputationAmount = data_get($mission, 'reputation_amount');
        if ($reputationAmount !== null) {
            $segments[] = Format::number($reputationAmount).' reputation XP';
        }

        $starSystems = data_get($mission, 'star_systems');
        if (is_array($starSystems) && $starSystems !== []) {
            $segments[] = 'in '.implode(', ', $starSystems);
        }

        $segments[] = 'View rewards, locations, and technical details.';

        return implode('. ', $segments);
    }

    private function buildRewardSegment(array $mission): ?string
    {
        $rewardMin = data_get($mission, 'reward_min');
        $rewardMax = data_get($mission, 'reward_max');
        $currency = data_get($mission, 'reward_currency') ?? 'aUEC';

        if ($rewardMin === null && $rewardMax === null) {
            return null;
        }

        if ($rewardMin !== null && $rewardMax !== null && $rewardMin === $rewardMax) {
            return 'Rewards '.Format::number((int) $rewardMax).' '.$currency;
        }

        $parts = [];
        if ($rewardMin !== null) {
            $parts[] = Format::number((int) $rewardMin);
        }

        $parts[] = $rewardMax !== null ? Format::number((int) $rewardMax) : '?';

        return 'Rewards '.implode('–', $parts).' '.$currency;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildLocation(array $mission): ?array
    {
        $starSystems = data_get($mission, 'star_systems');

        if (! is_array($starSystems) || $starSystems === []) {
            return null;
        }

        $places = array_map(static fn (string $system): array => [
            '@type' => 'Place',
            'name' => $system,
        ], $starSystems);

        return count($places) === 1 ? $places[0] : $places;
    }

    /**
     * @return array<string, mixed>|null
     */
    private function buildRewardOffer(array $mission): ?array
    {
        $rewardMin = data_get($mission, 'reward_min');
        $rewardMax = data_get($mission, 'reward_max');

        if ($rewardMin === null && $rewardMax === null) {
            return null;
        }

        $currency = data_get($mission, 'reward_currency') ?? 'aUEC';

        $priceSpec = [
            '@type' => 'QuantitativeValue',
            'unitText' => $currency,
        ];

        if ($rewardMin !== null && $rewardMax !== null && $rewardMin === $rewardMax) {
            $priceSpec['value'] = (int) $rewardMax;
        } else {
            if ($rewardMin !== null) {
                $priceSpec['minValue'] = (int) $rewardMin;
            }

            if ($rewardMax !== null) {
                $priceSpec['maxValue'] = (int) $rewardMax;
            }
        }

        return [
            '@type' => 'Offer',
            'priceSpecification' => $priceSpec,
        ];
    }

    /**
     * @return array<int, array<string, mixed>>|null
     */
    private function buildBlueprintInstrument(array $mission): ?array
    {
        $blueprints = data_get($mission, 'blueprints');
        $items = data_get($blueprints, 'items');

        if (! is_array($items) || $items === []) {
            return null;
        }

        $dropChancePercent = data_get($blueprints, 'drop_chance_percent');

        return array_map(static function (array $item) use ($dropChancePercent): array {
            $entry = [
                '@type' => 'Thing',
                'name' => data_get($item, 'name'),
            ];

            $itemUuid = data_get($item, 'uuid');
            if ($itemUuid !== null) {
                $entry['identifier'] = $itemUuid;
            }

            if ($dropChancePercent !== null) {
                $entry['probability'] = $dropChancePercent;
            }

            return $entry;
        }, $items);
    }

    private function firstStarSystem(array $mission): ?string
    {
        $starSystems = data_get($mission, 'star_systems');

        if (! is_array($starSystems) || $starSystems === []) {
            return null;
        }

        return $starSystems[0];
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

    private function buildCrimeStatRange(array $mission): ?string
    {
        $min = data_get($mission, 'min_crime_stat');
        $max = data_get($mission, 'max_crime_stat');

        if ($min === null && $max === null) {
            return null;
        }

        if ($min !== null && $max !== null && $min === $max) {
            return (string) $min;
        }

        return ($min ?? 0).'-'.($max ?? '?');
    }
}
