<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Starmap;

use App\Http\Resources\AbstractBaseResource;
use App\Models\StarCitizen\Starmap\CelestialObject;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'jumppoint',
    title: 'Jumppoint',
    description: 'A jumppoint from the starmap',
    properties: [
        new OA\Property(property: 'id', type: 'integer'),
        new OA\Property(property: 'size', type: 'string'),
        new OA\Property(property: 'direction', type: 'string'),
        new OA\Property(
            property: 'entry',
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'system_id', type: 'string'),
                new OA\Property(property: 'system_api_url', type: 'string'),
                new OA\Property(property: 'celestial_object_api_url', type: 'string'),
                new OA\Property(property: 'status', type: 'string'),
                new OA\Property(property: 'code', type: 'string'),
                new OA\Property(property: 'designation', type: 'string'),
            ],
        ),
        new OA\Property(
            property: 'exit',
            properties: [
                new OA\Property(property: 'id', type: 'integer'),
                new OA\Property(property: 'system_id', type: 'string'),
                new OA\Property(property: 'system_api_url', type: 'string'),
                new OA\Property(property: 'celestial_object_api_url', type: 'string'),
                new OA\Property(property: 'status', type: 'string'),
                new OA\Property(property: 'code', type: 'string'),
                new OA\Property(property: 'designation', type: 'string'),
            ],
        ),
    ],
    type: 'object'
)]
class JumppointResource extends AbstractBaseResource
{
    private bool $hideCO;

    public function __construct($resource, bool $hideCO = false)
    {
        parent::__construct($resource);
        $this->hideCO = $hideCO;
    }

    public function toArray($request): array
    {
        $entry = $this->whenLoaded('entry');
        $exit = $this->whenLoaded('exit');

        $entryData = $entry instanceof CelestialObject ? [
            'id' => $entry->cig_id,
            'system_id' => $entry->starsystem_id,
            'system_api_url' => route(
                'starsystems.show',
                ['code' => $entry->starsystem_id]
            ),
            'celestial_object_api_url' => route(
                'celestial-objects.show',
                ['code' => $entry->code]
            ),
            'status' => $this->entry_status,
            'code' => $entry->code,
            'designation' => $entry->designation,
        ] : null;

        $exitData = $exit instanceof CelestialObject ? [
            'id' => $exit->cig_id,
            'system_id' => $exit->starsystem_id,
            'system_api_url' => route(
                'starsystems.show',
                ['code' => $exit->starsystem_id]
            ),
            'celestial_object_api_url' => route(
                'celestial-objects.show',
                ['code' => $exit->code]
            ),
            'status' => $this->exit_status,
            'code' => $exit->code,
            'designation' => $exit->designation,
        ] : null;

        return [
            'id' => $this->cig_id,
            'name' => $this->name,
            'size' => $this->size,
            'direction' => $this->direction,
            'entry' => $entryData,
            'exit' => $exitData,
            $this->mergeWhen(! $this->hideCO, [
                'celestial_object_entry' => new CelestialObjectResource($this->whenLoaded('entry')),
                'celestial_object_exit' => new CelestialObjectResource($this->whenLoaded('exit')),
            ]),
        ];
    }
}
