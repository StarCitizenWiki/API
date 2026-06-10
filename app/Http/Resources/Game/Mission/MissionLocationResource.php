<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Mission;

use App\Http\Resources\AbstractBaseResource;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'mission_starmap_location_group',
    title: 'Mission Starmap Location Group',
    properties: [
        new OA\Property(property: 'purpose', type: 'string'),
        new OA\Property(
            property: 'locations',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/mission_starmap_location')
        ),
    ],
    type: 'object'
)]
#[OA\Schema(
    schema: 'mission_starmap_location',
    title: 'Mission Starmap Location',
    properties: [
        new OA\Property(property: 'uuid', type: 'string', format: 'uuid', nullable: true),
        new OA\Property(property: 'name', type: 'string', nullable: true),
        new OA\Property(property: 'system', type: 'string', nullable: true),
        new OA\Property(property: 'type', type: 'string', nullable: true),
        new OA\Property(property: 'link', type: 'string', format: 'uri', nullable: true),
        new OA\Property(property: 'web_url', type: 'string', format: 'uri', nullable: true),
    ],
    type: 'object'
)]
class MissionLocationResource extends AbstractBaseResource
{
    private const array PURPOSE_GROUP_MAP = [
        'Destinations' => ['Destination', 'Destination1', 'Destination2', 'Destination3', 'Destination4', 'DropoffDestination1', 'Dropoff1', 'GoToLocation'],
        'Locations' => ['Location', 'Location1', 'Location2', 'Location3', 'Location4', 'NearbyLocation', 'NeabyLocation', 'SubLocation'],
        'Availability' => ['availability', null],
    ];

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

    public function mapStarmapLocations(Collection $starmapLocations, Request $request): array
    {
        $grouped = [];

        foreach ($starmapLocations as $location) {
            $purpose = $location->pivot->purpose ?? null;
            $uuid = $location->location_uuid;

            $grouped[$purpose][] = $this->buildLocationData($location, $uuid, $request);
        }

        return collect($grouped)->map(fn (array $locations, ?string $purpose): array => [
            'purpose' => $purpose ?: 'Availability',
            'locations' => $locations,
        ])->values()->all();
    }

    public function mapMergedLocations(Collection $starmapLocations, Request $request): array
    {
        $merged = [];

        foreach ($starmapLocations as $location) {
            $purpose = $location->pivot->purpose ?? null;
            $matchedGroup = null;

            foreach (self::PURPOSE_GROUP_MAP as $label => $purposes) {
                if (in_array($purpose, $purposes, true)) {
                    $matchedGroup = $label;
                    break;
                }
            }

            $matchedGroup ??= ucfirst((string) ($purpose ?? 'Unknown'));

            $uuid = $location->location_uuid;
            $merged[$matchedGroup][] = $this->buildLocationData($location, $uuid, $request);
        }

        return $merged;
    }

    private function buildLocationData(mixed $location, ?string $uuid, Request $request): array
    {
        return [
            'uuid' => $uuid,
            'name' => $location->name,
            'system' => $location->system,
            'type' => $location->type_name,
            'link' => $uuid !== null
                ? ($this->makeApiUrl)(
                    'locations.show',
                    ['identifier' => $uuid],
                    $request,
                )
                : null,
            'web_url' => $uuid !== null
                ? ($this->makeWebUrl)(
                    'web.locations.show',
                    ['identifier' => $uuid],
                    $request,
                )
                : null,
        ];
    }
}
