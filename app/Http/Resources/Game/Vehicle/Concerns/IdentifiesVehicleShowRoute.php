<?php

declare(strict_types=1);

namespace App\Http\Resources\Game\Vehicle\Concerns;

use Illuminate\Http\Request;

trait IdentifiesVehicleShowRoute
{
    private function isVehicleShowRoute(Request $request): bool
    {
        return $request->routeIs('vehicles.show', '*.vehicles.show', 'ground-vehicles.show', 'gravlev-vehicles.show');
    }
}
