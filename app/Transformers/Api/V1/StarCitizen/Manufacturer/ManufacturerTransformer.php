<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizen\Manufacturer;

use App\Models\StarCitizen\Manufacturer\Manufacturer;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Transformers\Api\V1\StarCitizen\AbstractTranslationTransformer as TranslationTransformer;
use App\Transformers\Api\V1\StarCitizen\Vehicle\VehicleLinkTransformer;
use League\Fractal\Resource\Collection;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'manufacturer',
    title: 'Manufacturer',
    description: 'An in-game vehicle manufacturer',
    properties: [
        new OA\Property(property: 'code', type: 'string'),
        new OA\Property(property: 'name', type: 'string'),
        new OA\Property(property: 'known_for', type: 'string'),
        new OA\Property(property: 'description', type: 'string'),
        new OA\Property(
            property: 'ships',
            properties: [
                new OA\Property(
                    property: 'ships',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/vehicle_link',
                            type: 'array',
                            items: new OA\Items(),
                        ),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
        ),
        new OA\Property(
            property: 'vehicles',
            properties: [
                new OA\Property(
                    property: 'vehicles',
                    properties: [
                        new OA\Property(
                            property: 'data',
                            ref: '#/components/schemas/vehicle_link',
                            type: 'array',
                            items: new OA\Items(),
                        ),
                    ],
                    type: 'object',
                    nullable: true
                ),
            ],
        ),
    ],
    type: 'object'
)]
class ManufacturerTransformer extends TranslationTransformer
{
    protected $availableIncludes = [
        'ships',
        'vehicles',
    ];

    /**
     * @param Manufacturer $manufacturer
     *
     * @return array
     */
    public function transform(Manufacturer $manufacturer): array
    {
        $this->missingTranslations = [];
        $translations = $this->getTranslation($manufacturer);

        return [
            'code' => $manufacturer->name_short,
            'name' => $manufacturer->name,
            'known_for' => $translations['known_for'],
            'description' => $translations['description'],
            'missing_translations' => $this->missingTranslations,
        ];
    }

    /**
     * If a valid locale code is set this function will return the corresponding translation or use english as a
     * fallback
     *
     * @param HasTranslations $model
     *
     * @param string          $translationKey
     *
     * @return array|string the Translation
     */
    protected function getTranslation(HasTranslations $model, $translationKey = 'translation')
    {
        return parent::getTranslation($model, ['known_for', 'description']);
    }

    /**
     * @param Manufacturer $manufacturer
     *
     * @return Collection
     */
    public function includeShips(Manufacturer $manufacturer): Collection
    {
        return $this->collection($manufacturer->ships, new VehicleLinkTransformer());
    }

    /**
     * @param Manufacturer $manufacturer
     *
     * @return Collection
     */
    public function includeVehicles(Manufacturer $manufacturer): Collection
    {
        return $this->collection($manufacturer->vehicles, new VehicleLinkTransformer());
    }
}
