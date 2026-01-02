<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'counter_measure',
    title: 'Counter Measure',
    description: 'Counter measure launcher ammo capacity values derived from stdItem.Ammunition or raw ammo container data.',
    properties: [
        new OA\Property(property: 'initial_ammo_count', type: 'integer', nullable: true),
        new OA\Property(property: 'max_ammo_count', type: 'integer', nullable: true),
    ],
    type: 'object'
)]
class CounterMeasureResource extends AbstractItemSpecificationResource
{
    public function toArray(Request $request): array
    {
        $data = $this->parseSpecificationData($this->resource['data'] ?? $this->resource->data ?? null);
        $stdItem = $this->extractStdItem($data);
        $ammunition = Arr::get($stdItem, 'Ammunition', []);
        $rawAmmo = Arr::get($data, 'Raw.Entity.Components.SAmmoContainerComponentParams', []);

        $initial = Arr::get($ammunition, 'InitialCapacity', Arr::get($rawAmmo, 'initialAmmoCount'));
        $max = Arr::get($ammunition, 'Capacity', Arr::get($rawAmmo, 'maxAmmoCount'));

        if ($max === 0 || $max === null) {
            $max = Arr::get($rawAmmo, 'maxRestockCount', $max);
        }

        return [
            'initial_ammo_count' => $initial,
            'max_ammo_count' => $max,
        ];
    }
}
