<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix;

class ProductionNote extends AbstractShipMatrixLookup
{
    protected $table = 'shipmatrix_production_notes';

    protected $fillable = [
        'translation',
    ];
}
