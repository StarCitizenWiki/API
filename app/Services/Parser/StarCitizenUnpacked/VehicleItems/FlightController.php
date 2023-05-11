<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class FlightController extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'IFCSParams');

        if ($data === null) {
            return null;
        }

        return array_filter([
            'max_speed' => Arr::get($data, 'maxSpeed'),
            'scm_speed' => Arr::get($data, 'scmSpeed'),
            'pitch' => Arr::get($data, 'maxAngularVelocity.x'),
            'roll' => Arr::get($data, 'maxAngularVelocity.y'),
            'yaw' => Arr::get($data, 'maxAngularVelocity.z'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
