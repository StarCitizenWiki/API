<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Starmap;

use App\Models\StarCitizen\Starmap\Jumppoint\Jumppoint;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer;
use League\Fractal\Resource\Item;
use Throwable;
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
class JumppointTransformer extends AbstractTranslationTransformer
{
    protected $availableIncludes = [
        'celestial_object_entry',
        'celestial_object_exit',
    ];

    /**
     * @param Jumppoint $jumppoint
     *
     * @return array
     */
    public function transform(Jumppoint $jumppoint): array
    {
        return [
            'id' => $jumppoint->cig_id,
            'size' => $jumppoint->size,
            'direction' => $jumppoint->direction,
            'entry' => [
                'id' => $jumppoint->entry->cig_id,
                'system_id' => $jumppoint->entry->starsystem_id,
                'system_api_url' => $this->makeApiUrl(
                    self::STARMAP_STARSYSTEM_SHOW,
                    $jumppoint->entry->starsystem_id
                ),
                'celestial_object_api_url' => $this->makeApiUrl(
                    self::STARMAP_CELESTIAL_OBJECTS_SHOW,
                    $jumppoint->entry->code
                ),
                'status' => $jumppoint->entry_status,
                'code' => $jumppoint->entry->code,
                'designation' => $jumppoint->entry->designation,
            ],
            'exit' => [
                'id' => $jumppoint->exit->cig_id,
                'system_id' => $jumppoint->exit->starsystem_id,
                'system_api_url' => $this->makeApiUrl(
                    self::STARMAP_STARSYSTEM_SHOW,
                    $jumppoint->exit->starsystem_id
                ),
                'celestial_object_api_url' => $this->makeApiUrl(
                    self::STARMAP_CELESTIAL_OBJECTS_SHOW,
                    $jumppoint->exit->code
                ),
                'status' => $jumppoint->exit_status,
                'code' => $jumppoint->exit->code,
                'designation' => $jumppoint->exit->designation,
            ],
        ];
    }

    /**
     * The celestial object of the jump point entry
     *
     * @param Jumppoint $jumppoint
     *
     * @return Item
     * @throws Throwable
     */
    public function includeCelestialObjectEntry(Jumppoint $jumppoint): Item
    {
        return $this->item($jumppoint->entry, $this->makeTransformer(CelestialObjectTransformer::class, $this));
    }

    /**
     * The celestial object of the jump point exit
     *
     * @param Jumppoint $jumppoint
     *
     * @return Item
     * @throws Throwable
     */
    public function includeCelestialObjectExit(Jumppoint $jumppoint): Item
    {
        return $this->item($jumppoint->exit, $this->makeTransformer(CelestialObjectTransformer::class, $this));
    }
}
