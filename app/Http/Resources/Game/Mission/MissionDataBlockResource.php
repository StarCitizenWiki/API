<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Mission;

use App\Http\Resources\AbstractBaseResource;

/**
 * Pure data resource for small mission sub-shapes that don't warrant their own Resource class.
 * Standing, tokens, deadline, broker reputation prerequisites, item counts, and entity spawns.
 */
class MissionDataBlockResource extends AbstractBaseResource
{
    public static function mapStanding(mixed $standing): ?array
    {
        if (! is_array($standing)) {
            return null;
        }

        return [
            'name' => $standing['Name'] ?? null,
            'min_reputation' => $standing['MinReputation'] ?? null,
        ];
    }

    public static function mapMissionTokens(mixed $tokens): ?array
    {
        if (! is_array($tokens) || empty($tokens)) {
            return null;
        }

        $destinations = $tokens['Destination'] ?? [];

        if (! is_array($destinations) || empty($destinations)) {
            return null;
        }

        return [
            'destinations' => $destinations,
        ];
    }

    public static function mapDeadline(mixed $deadline): ?array
    {
        if (! is_array($deadline)) {
            return null;
        }

        return [
            'auto_end' => $deadline['AutoEnd'] ?? null,
            'end_reason' => $deadline['EndReason'] ?? null,
            'completion_time_minutes' => $deadline['CompletionTime'] ?? null,
            'result_after_timer' => $deadline['ResultAfterTimer'] ?? null,
        ];
    }

    public static function mapBrokerReputationPrerequisites(mixed $prerequisites): ?array
    {
        if (! is_array($prerequisites)) {
            return null;
        }

        return [
            'max_wanted_level' => $prerequisites['MaxWantedLevel'] ?? null,
            'min_wanted_level' => $prerequisites['MinWantedLevel'] ?? null,
        ];
    }

    public static function mapItemCounts(mixed $counts): ?array
    {
        if (! is_array($counts)) {
            return null;
        }

        return [
            'max_items' => $counts['MaxItems'] ?? null,
            'min_items' => $counts['MinItems'] ?? null,
        ];
    }

    public static function mapEntitySpawns(mixed $spawns): ?array
    {
        if (! is_array($spawns) || empty($spawns)) {
            return null;
        }

        return array_map(static function (array $spawn): array {
            $tags = $spawn['Tags'] ?? [];
            $markupTags = $spawn['MarkupTags'] ?? [];

            $tagNames = array_filter(array_map(
                static fn (array $tag): ?string => $tag['Name'] ?? null,
                is_array($tags) ? $tags : [],
            ));
            $markupTagNames = array_filter(array_map(
                static fn (array $tag): ?string => $tag['Name'] ?? null,
                is_array($markupTags) ? $markupTags : [],
            ));

            return [
                'tags' => $tags,
                'amount' => $spawn['Amount'] ?? null,
                'weight' => $spawn['Weight'] ?? null,
                'group_name' => $spawn['GroupName'] ?? null,
                'markup_tags' => $markupTags,
                'negative_tags' => $spawn['NegativeTags'] ?? null,
                'merged_tags' => array_values(array_unique(array_merge($tagNames, $markupTagNames))),
            ];
        }, $spawns);
    }
}
