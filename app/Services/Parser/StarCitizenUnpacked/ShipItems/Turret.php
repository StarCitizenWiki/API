<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

final class Turret extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        /** @var Collection|null $data */
        $data = $rawData->pull('Components.SItemPortContainerComponentParams');
        if ($data === null) {
            return null;
        }

        $min = PHP_INT_MAX;
        $max = 0;

        $data = collect($data['Ports']);

        $data->filter(function ($component) {
            return isset($component['MinSize'], $component['MaxSize']);
        })
            ->each(function ($component) use (&$min, &$max) {
                if ((int)$component['MinSize'] < $min) {
                    $min = (int)$component['MinSize'];
                }
                if ((int)$component['MaxSize'] > $max) {
                    $max = (int)$component['MaxSize'];
                }
            });

        if ($min === PHP_INT_MAX) {
            $min = 0;
        }

        return [
            'min_size' => $min,
            'max_size' => $max,
            'max_mounts' => $data->count(),
        ];
    }
}
