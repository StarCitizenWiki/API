<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\ProductionStatus;

use Illuminate\Database\Eloquent\Model;

class VehicleProductionStatusTranslation extends Model
{
    public function productionStatus()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\ProductionStatus\VehicleProductionStatus');
    }
}
