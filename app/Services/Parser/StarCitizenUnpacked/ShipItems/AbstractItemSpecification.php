<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\ShipItems;

use Illuminate\Support\Collection;

abstract class AbstractItemSpecification
{
    abstract public static function getData(array $item, Collection $rawData): ?array;
}
