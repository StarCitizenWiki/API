<?php

declare(strict_types=1);

use App\Models\StarCitizen\Starmap\CelestialObject;

it('has custom route key name', function () {
    $model = new CelestialObject;
    expect($model->getRouteKeyName())->toBe('code');
});
