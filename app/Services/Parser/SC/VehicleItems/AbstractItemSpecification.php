<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

abstract class AbstractItemSpecification
{
    abstract public static function getData(Collection $item): ?array;

    protected static function getAttachDef(Collection $item): ?array
    {
        return self::get($item, 'SAttachableComponentParams.AttachDef');
    }

    protected static function get(Collection $item, string $key): ?array
    {
        return Arr::get($item, 'Raw.Entity.Components.' . $key);
    }
}
