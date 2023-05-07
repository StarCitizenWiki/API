<?php

declare(strict_types=1);

namespace App\Http\Resources\SC\Item;

use App\Http\Resources\AbstractTranslationResource;
use App\Http\Resources\SC\ItemSpecification\CoolerResource;
use App\Http\Resources\SC\ItemSpecification\FlightControllerResource;
use App\Http\Resources\SC\ItemSpecification\PowerPlantResource;
use App\Http\Resources\SC\ItemSpecification\SelfDestructResource;
use App\Http\Resources\SC\ItemSpecification\ThrusterResource;
use App\Http\Resources\SC\Shop\ShopResource;
use Illuminate\Http\Request;

class ItemResource extends AbstractTranslationResource
{
    public static function validIncludes(): array
    {
        return [
            'shops',
            'shops.items'
        ];
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     * @return array
     */
    public function toArray($request): array
    {
        if ($this->uuid === null) {
            return [];
        }

        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'description' => $this->getTranslation($this, $request),
            'size' => $this->size,
            'manufacturer' => $this->manufacturer,
            'type' => $this->type,
            'sub_type' => $this->sub_type,
            'dimension' => new ItemDimensionResource($this),
            $this->mergeWhen($this->container->exists, [
                'container' => new ItemContainerResource($this->container),
            ]),
            'ports' => ItemPortResource::collection($this->whenLoaded('ports')),
            $this->mergeWhen($this->relationLoaded('heatData'), [
                'heat' => new ItemHeatDataResource($this->heatData),
            ]),
            $this->mergeWhen($this->relationLoaded('powerData'), [
                'power' => new ItemPowerDataResource($this->powerData),
            ]),
            $this->mergeWhen($this->relationLoaded('distortionData'), [
                'distortion' => new ItemDistortionDataResource($this->distortionData),
            ]),
//            $this->mergeWhen($this->relationLoaded('durabilityData'), [
//                'durability' => new ItemDurabilityDataResource($this->durabilityData),
//            ]),
            $this->mergeWhen(...$this->addSpecification()),
            'shops' => ShopResource::collection($this->whenLoaded('shops')),
            'version' => $this->version,
        ];
    }

    private function addSpecification(): array
    {
        switch ($this->type) {
            case 'Cooler':
                return [
                    $this->specification->exists,
                    [
                        'cooler' => new CoolerResource($this->specification),
                    ]
                ];

            case 'MainThruster':
            case 'ManneuverThruster':
                return [
                    $this->specification->exists,
                    [
                        'thruster' => new ThrusterResource($this->specification),
                    ]
                ];

            case 'PowerPlant':
                return [
                    $this->specification->exists,
                    [
                        'power_plant' => new PowerPlantResource($this->specification),
                    ]
                ];

            case 'SelfDestruct':
                return [
                    $this->specification->exists,
                    [
                        'self_destruct' => new SelfDestructResource($this->specification),
                    ]
                ];

            case 'FlightController':
                return [
                    $this->specification->exists,
                    [
                        'flight_controller' => new FlightControllerResource($this->specification),
                    ]
                ];
            default:
                return [false, []];
        }
    }
}
