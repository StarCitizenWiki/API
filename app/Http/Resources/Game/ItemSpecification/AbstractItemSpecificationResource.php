<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\ItemSpecification;

use App\Http\Resources\AbstractBaseResource;
use Illuminate\Support\Arr;

abstract class AbstractItemSpecificationResource extends AbstractBaseResource
{
    /**
     * Parse specification data that may be an array or object into an array.
     *
     * Handles the common pattern where specification data can be stored
     * as either array or object format.
     */
    protected function parseSpecificationData(mixed $data): array
    {
        if ($data === null) {
            return [];
        }

        if (is_array($data)) {
            return $data;
        }

        if (is_object($data) && method_exists($data, 'toArray')) {
            return $data->toArray();
        }

        return [];
    }

    protected function extractStdItem(array $data): array
    {
        $stdItem = Arr::get($data, 'stdItem', []);

        if ($stdItem === [] && Arr::has($data, 'Item.stdItem')) {
            $stdItem = Arr::get($data, 'Item.stdItem', []);
        }

        return is_array($stdItem) ? $stdItem : [];
    }

    /**
     * Build standardized damage array from damage data.
     *
     * Transforms damage data into the expected API format with
     * physical, energy, distortion, thermal, biochemical, and stun values.
     * Filters out damage types with zero values.
     */
    protected function buildDamageArray(array $damageData): array
    {
        $damages = [];
        $damageTypes = ['Physical', 'Energy', 'Distortion', 'Thermal', 'Biochemical', 'Stun'];

        foreach ($damageTypes as $type) {
            $damageValue = Arr::get($damageData, $type, 0);
            if ($damageValue > 0) {
                $damages[] = [
                    'type' => $type,
                    'name' => $type,
                    'damage' => $damageValue,
                ];
            }
        }

        return $damages;
    }

    /**
     * Calculate total damage from damage data array.
     */
    protected function calculateTotalDamage(array $damageData): float
    {
        $total = 0;
        $damageTypes = ['Physical', 'Energy', 'Distortion', 'Thermal', 'Biochemical', 'Stun'];

        foreach ($damageTypes as $type) {
            $total += Arr::get($damageData, $type, 0);
        }

        return $total;
    }
}
