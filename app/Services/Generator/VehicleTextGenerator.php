<?php

declare(strict_types=1);

namespace App\Services\Generator;

use App\Models\StarCitizen\Vehicle\Vehicle\Vehicle;

final class VehicleTextGenerator
{
    const NUMBER_MAPPINGS = [
        1 => 'einem',
        2 => 'zwei',
        3 => 'drei',
        4 => 'vier',
        5 => 'fünf',
    ];

    const SIZE_MAPPINGS = [
        'small' => 'kleinen',
        'medium' => 'mittleren',
        'large' => 'großen',
        'capital' => 'kapitalen',
    ];

    private Vehicle $vehicle;

    public function __construct(Vehicle $vehicle)
    {
        $this->vehicle = $vehicle;
    }
}
