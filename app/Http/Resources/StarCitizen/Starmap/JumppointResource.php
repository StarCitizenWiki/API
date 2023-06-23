<?php

declare(strict_types=1);

namespace App\Http\Resources\StarCitizen\Starmap;

use App\Http\Resources\AbstractTranslationResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'jumppoint_v2',
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
class JumppointResource extends AbstractTranslationResource
{
    private bool $hideCO;

    public function __construct($resource, bool $hideCO = false)
    {
        parent::__construct($resource);
        $this->hideCO = $hideCO;
    }

    public function toArray($request): array
    {
        return [
            'id' => $this->cig_id,
            'size' => $this->size,
            'direction' => $this->direction,
            'entry' => [
                'id' => $this->entry->cig_id,
                'system_id' => $this->entry->starsystem_id,
                'system_api_url' => $this->makeApiUrl(
                    self::STARMAP_STARSYSTEM_SHOW,
                    $this->entry->starsystem_id
                ),
                'celestial_object_api_url' => $this->makeApiUrl(
                    self::STARMAP_CELESTIAL_OBJECTS_SHOW,
                    $this->entry->code
                ),
                'status' => $this->entry_status,
                'code' => $this->entry->code,
                'designation' => $this->entry->designation,
            ],
            'exit' => [
                'id' => $this->exit->cig_id,
                'system_id' => $this->exit->starsystem_id,
                'system_api_url' => $this->makeApiUrl(
                    self::STARMAP_STARSYSTEM_SHOW,
                    $this->exit->starsystem_id
                ),
                'celestial_object_api_url' => $this->makeApiUrl(
                    self::STARMAP_CELESTIAL_OBJECTS_SHOW,
                    $this->exit->code
                ),
                'status' => $this->exit_status,
                'code' => $this->exit->code,
                'designation' => $this->exit->designation,
            ],
            $this->mergeWhen(!$this->hideCO, [
                'celestial_object_entry' => new CelestialObjectResource($this->whenLoaded('entry')),
                'celestial_object_exit' => new CelestialObjectResource($this->whenLoaded('exit')),
            ]),
        ];
    }
}
