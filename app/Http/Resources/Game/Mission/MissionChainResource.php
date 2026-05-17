<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Mission;

use App\Http\Resources\AbstractBaseResource;
use App\Support\Formatting\FormatMissionTitle;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mission_completion_tag',
    title: 'Mission Completion Tag',
    properties: [
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(
            property: 'unlocks_missions',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_completion_tag_mission')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_completion_tag_mission',
    title: 'Mission Completion Tag Mission',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid'),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_prerequisite_group',
    title: 'Mission Prerequisite Group',
    properties: [
        new OA\Property(property: 'required_count', type: 'integer', nullable: true),
        new OA\Property(
            property: 'required_tags',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'name', type: 'string', nullable: true),
                    new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
                ],
                type: 'object'
            )
        ),
        new OA\Property(
            property: 'excluded_tags',
            type: 'array',
            items: new OA\Items(
                properties: [
                    new OA\Property(property: 'name', type: 'string', nullable: true),
                    new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
                ],
                type: 'object'
            )
        ),
        new OA\Property(
            property: 'missions',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_chain_link')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_unlock_group',
    title: 'Mission Unlock Group',
    description: 'A completion tag group that unlocks missions when this mission is completed.',
    properties: [
        new OA\Property(property: 'tag_name', type: 'string', nullable: true),
        new OA\Property(property: 'tag_uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(
            property: 'missions',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_chain_link')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_chain_link',
    title: 'Mission Chain Link',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'title', type: 'string', nullable: true),
        new OA\Property(property: 'mission_type', type: 'string', nullable: true),
        new OA\Property(property: 'variant_count', description: 'Number of mission variants with the same title. Only present when greater than 1.', type: 'integer', nullable: true),
        new OA\Property(
            property: 'variants',
            description: 'Additional mission variants sharing the same title. Only present when variant_count > 1.',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_chain_variant'),
            nullable: true
        ),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_chain_variant',
    title: 'Mission Chain Variant',
    description: 'A variant of a mission chain link, sharing the same title but a different UUID.',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
class MissionChainResource extends AbstractBaseResource
{
    /**
     * @param  Closure(string, array<string, string>, Request): string  $makeApiUrl
     * @param  Closure(string, array<string, string>, Request): string  $makeWebUrl
     */
    public function __construct(
        $resource,
        private readonly Closure $makeApiUrl,
        private readonly Closure $makeWebUrl,
    ) {
        parent::__construct($resource);
    }

    public function mapPrerequisiteGroups(Collection $prerequisiteGroups, Request $request): array
    {
        return $prerequisiteGroups->map(function ($group) use ($request): array {
            return [
                'required_count' => $group->required_count,
                'required_tags' => $group->tags->where('type', 'required')->values()->map(fn ($tag): array => [
                    'name' => $tag->tag_name,
                    'uuid' => $tag->tag_uuid,
                ])->all(),
                'excluded_tags' => $group->tags->where('type', 'excluded')->values()->map(fn ($tag): array => [
                    'name' => $tag->tag_name,
                    'uuid' => $tag->tag_uuid,
                ])->all(),
                'missions' => $this->groupChainMissions($group->missions, $request),
            ];
        })->values()->all();
    }

    public function mapUnlockGroups(Collection $unlockGroups, Request $request): array
    {
        return $unlockGroups->map(function ($group) use ($request): array {
            return [
                'tag_name' => $group->tag_name,
                'tag_uuid' => $group->tag_uuid,
                'missions' => $this->groupChainMissions($group->missions, $request),
            ];
        })->values()->all();
    }

    public function groupChainMissions(Collection $missions, Request $request): array
    {
        $mapped = $missions->map(function ($groupMission) use ($request): array {
            $linked = $groupMission->linkedMissionData;

            return [
                'uuid' => $linked?->mission?->uuid,
                'title' => FormatMissionTitle::format($linked?->title, $linked?->debug_name),
                'raw_title' => $linked?->title,
                'mission_type' => $linked?->mission_type,
                'link' => $linked?->mission?->uuid !== null
                    ? ($this->makeApiUrl)(
                        'missions.show',
                        ['mission' => $linked->mission->uuid],
                        $request,
                    )
                    : null,
                'web_url' => $linked?->mission?->uuid !== null
                    ? ($this->makeWebUrl)(
                        'web.missions.show',
                        ['mission' => $linked->mission->slug ?? $linked->mission->uuid],
                        $request,
                    )
                    : null,
            ];
        })->values()->all();

        return collect($mapped)
            ->groupBy(fn (array $m): string => $m['raw_title'] ?? '__ungrouped__')
            ->flatMap(function ($group, string $title): array {
                if ($title === '__ungrouped__' || blank($title)) {
                    return $group->map(fn (array $m) => collect($m)->forget('raw_title')->all())->all();
                }

                $representative = $group->first();
                $variants = $group->skip(1)->map(fn (array $m): array => [
                    'uuid' => $m['uuid'],
                    'link' => $m['link'],
                    'web_url' => $m['web_url'],
                ])->values()->all();

                $result = collect($representative)->forget('raw_title')->all();

                if ($group->count() > 1) {
                    $result['variant_count'] = $group->count();
                    $result['variants'] = $variants;
                }

                return [$result];
            })
            ->values()
            ->all();
    }

    public function mapCompletionTags(mixed $data, Request $request): ?array
    {
        $tags = $data?->get('CompletionTags');

        if (! is_array($tags) || empty($tags)) {
            return null;
        }

        return collect($tags)
            ->filter(fn (array $tag): bool => ! empty($tag['UnlocksMissions']))
            ->map(function (array $tag) use ($request): array {
                return [
                    'name' => $tag['Name'] ?? null,
                    'unlocks_missions' => collect($tag['UnlocksMissions'] ?? [])->map(function (array $m) use ($request): array {
                        return [
                            'uuid' => $m['UUID'] ?? null,
                            'title' => $m['Title'] ?? null,
                            'link' => isset($m['UUID'])
                                ? ($this->makeApiUrl)(
                                    'missions.show',
                                    ['mission' => $m['UUID']],
                                    $request,
                                )
                                : null,
                            'web_url' => isset($m['UUID'])
                                ? ($this->makeWebUrl)(
                                    'web.missions.show',
                                    ['mission' => $m['UUID']],
                                    $request,
                                )
                                : null,
                        ];
                    })->values()->all(),
                ];
            })->values()->all();
    }
}
