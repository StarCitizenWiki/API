<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix;

class ProductionStatus extends AbstractShipMatrixLookup
{
    protected $table = 'shipmatrix_production_statuses';

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
