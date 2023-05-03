<?php

declare(strict_types=1);

namespace App\Transformers\Api\V1\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\VehicleHardpoint;
use App\Transformers\Api\V1\StarCitizenUnpacked\ShipItem\ShipItemTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use League\Fractal\Resource\Collection;

/**
 * Transformer for hardpoints on a vehicle
 * Adds the hardpoint name, its min- and max size as well as the equipped item if available
 */
class VehicleHardpointTransformer extends AbstractCommodityTransformer
{
    private array $manualTypes = [
        'Turret',
        'TurretBase',
        'ToolArm',
        'MiningArm',
        'WeaponMount',
        'Container',
    ];

    public function transform(VehicleHardpoint $hardpoint): array
    {
        $this->defaultIncludes = [];

        $data = [
            'name' => $hardpoint->hardpoint_name,
            'min_size' => $hardpoint->min_size,
            'max_size' => $hardpoint->max_size,
            'class_name' => $hardpoint->class_name,
            'health' => optional($hardpoint->shipItem)->health,
        ];

        if (
            $hardpoint->shipItem->hasSpecification() ||
            ($hardpoint->shipItem->item !== null && in_array($hardpoint->shipItem->item->type, $this->manualTypes, true))
        ) {
            $data += [
                'type' => $hardpoint->shipItem->item->type,
                'sub_type' => $hardpoint->shipItem->item->sub_type,
            ];

            $this->defaultIncludes[] = 'item';
        }

        $data = array_filter($data);

        $this->defaultIncludes[] = 'children';

        return $data;
    }

    public function includeItem(VehicleHardpoint $hardpoint)
    {
        if ($hardpoint->shipItem->item->type === 'Container') {
            return $this->item($hardpoint->shipItem, function ($shipItem) {
                return [
                    'uuid' => $shipItem->uuid,
                    'name' => $shipItem->item->name,
                    'size' => $shipItem->item->size,
                    'manufacturer' => $shipItem->item->manufacturer,
                    'type' => $shipItem->type,
                    'sub_type' => $shipItem->item->sub_type,
                    'cargo_grid' => [
                        'scu' => $shipItem->item->container->scu,
                    ]
                ];
            });
        } elseif (in_array($hardpoint->shipItem->item->type, $this->manualTypes, true)) {
            /** @var \Illuminate\Support\Collection $ports */
            $ports = $hardpoint->shipItem->ports;

            return $this->item($ports, function (\Illuminate\Support\Collection $ports) use ($hardpoint) {
                return [
                    'uuid' => $hardpoint->shipItem->uuid,
                    'name' => $hardpoint->shipItem->item->name,
                    'size' => $hardpoint->shipItem->item->size,
                    'manufacturer' => $hardpoint->shipItem->item->manufacturer,
                    'type' => $hardpoint->shipItem->type,
                    'sub_type' => $hardpoint->shipItem->item->sub_type,
                    'turret' => [
                        'max_mounts' => count($ports),
                        'min_size' => $ports->min('min_size'),
                        'max_size' => $ports->max('max_size'),
                    ],
                ];
            });
        }

        if (!$hardpoint->shipItem->hasSpecification()) {
            return $this->null();
        }

        $transformer = new ShipItemTransformer();
        $transformer->excludeDefaults();

        try {
            return $this->item($hardpoint->shipItem->specification, $transformer);
        } catch (ModelNotFoundException $e) {
            return $this->null();
        }
    }

    public function includeChildren(VehicleHardpoint $hardpoint): Collection
    {
        return $this->collection($hardpoint->children2, new self());
    }
}
