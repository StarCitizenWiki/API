<?php

declare(strict_types=1);

namespace App\Support\Seo;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

final class MissionShowSeoData extends AbstractShowSeoData
{
    /**
     * @return array<string, mixed>
     */
    public function build(array $mission, Request $request): array
    {
        $title = $this->normalizeString(data_get($mission, 'title')) ?? 'Mission';
        $type = $this->normalizeString(data_get($mission, 'mission_type'));
        $factionName = $this->normalizeString(data_get($mission, 'faction.name'));
        $missionGiver = $this->normalizeString(data_get($mission, 'mission_giver'));
        $legalityLabel = $this->normalizeString(data_get($mission, 'legality_label'));
        $uuid = $this->normalizeString(data_get($mission, 'uuid'));
        $description = $this->resolveDescription(data_get($mission, 'description'));
        $version = $this->resolveVersionCode($request);
        $canonicalUrl = $this->normalizeString(data_get($mission, 'web_url'))
            ?? $this->fallbackShowUrl($uuid, $version);

        $breadcrumbs = $this->buildBreadcrumbs($title, $factionName, $canonicalUrl, $version);
        $metaTitle = $this->buildMetaTitle($title, $type);
        $metaDescription = Str::limit(
            $description ?? $this->buildFallbackDescription($title, $type, $factionName, $mission),
            160,
        );

        return [
            'title' => $metaTitle,
            'metaDescription' => $metaDescription,
            'canonicalUrl' => $canonicalUrl,
            'keywords' => $this->compactValues([
                $title,
                $type,
                $factionName,
                $missionGiver,
                $legalityLabel,
                'Star Citizen',
                'SC',
            ]),
            'ogTitle' => $metaTitle,
            'ogDescription' => $metaDescription,
            'twitterTitle' => $metaTitle,
            'twitterDescription' => $metaDescription,
            'breadcrumbs' => $breadcrumbs,
            'structuredData' => $this->compactValues([
                $this->buildBreadcrumbStructuredData($breadcrumbs),
                $this->buildMissionStructuredData(
                    title: $title,
                    type: $type,
                    factionName: $factionName,
                    metaDescription: $metaDescription,
                    canonicalUrl: $canonicalUrl,
                    mission: $mission,
                    version: $this->normalizeString(data_get($mission, 'game_version')),
                ),
            ]),
        ];
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

    private function buildMetaTitle(string $title, ?string $type): string
    {
        $detail = $type ?? 'Mission';

        return $this->joinSegments([$title, $detail, 'Star Citizen Mission'], ' | ');
    }

    private function buildFallbackDescription(string $title, ?string $type, ?string $factionName, array $mission): string
    {
        $typeLabel = $type ?? 'mission';
        $base = 'Browse Star Citizen '.$typeLabel.' mission data for '.$title;

        if ($factionName !== null) {
            $base .= ' from '.$factionName;
        }

        $reputationAmount = $this->normalizeInt(data_get($mission, 'reputation_amount'));
        if ($reputationAmount !== null) {
            $base .= '. '.number_format($reputationAmount).' reputation XP';
        }

        $base .= '. View rewards, locations, and technical details.';

        return $base;
    }

    /**
     * @return array<string, mixed>
     */
    private function buildMissionStructuredData(
        string $title,
        ?string $type,
        ?string $factionName,
        string $metaDescription,
        string $canonicalUrl,
        array $mission,
        ?string $version,
    ): array {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Action',
            'name' => $title,
            'description' => $metaDescription,
            'url' => $canonicalUrl,
        ];

        if ($factionName !== null) {
            $schema['agent'] = [
                '@type' => 'Organization',
                'name' => $factionName,
            ];
        }

        $additionalProperty = $this->buildPropertyValues([
            'Type' => $type,
            'Faction' => $factionName,
            'Reputation XP' => $this->normalizeInt(data_get($mission, 'reputation_amount')),
            'Legality' => $this->normalizeString(data_get($mission, 'legality_label')),
            'Has Combat' => data_get($mission, 'has_combat') === true ? 'Yes' : null,
            'Enemy Count' => $this->buildEnemyCountRange($mission),
            'Rank' => $this->normalizeInt(data_get($mission, 'rank_index')),
            'Time to Complete' => $this->buildTimeToComplete(data_get($mission, 'time_to_complete_minutes')),
            'Version' => $version,
        ]);

        if ($additionalProperty !== []) {
            $schema['additionalProperty'] = $additionalProperty;
        }

        return $schema;
    }

    private function buildEnemyCountRange(array $mission): ?string
    {
        $min = $this->normalizeInt(data_get($mission, 'enemy_count_min'));
        $max = $this->normalizeInt(data_get($mission, 'enemy_count_max'));

        if ($min === null && $max === null) {
            return null;
        }

        if ($min !== null && $max !== null && $min === $max) {
            return (string) $min;
        }

        return ($min ?? 0).'-'.($max ?? '?');
    }

    private function buildTimeToComplete(mixed $minutes): ?string
    {
        $mins = $this->normalizeInt($minutes);

        if ($mins === null) {
            return null;
        }

        if ($mins >= 60) {
            $hours = intdiv($mins, 60);
            $remaining = $mins % 60;

            return $remaining > 0
                ? $hours.'h '.$remaining.'m'
                : $hours.'h';
        }

        return $mins.'m';
    }

    protected function fallbackShowUrl(?string $uuid, ?string $version): string
    {
        if ($uuid === null) {
            return url()->current();
        }

        return route('web.missions.show', array_filter([
            'mission' => $uuid,
            'version' => $version,
        ]));
    }
}
