<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Focus;

use Illuminate\Database\Eloquent\Model;

class VehicleFocusTranslation extends Model
{
    public function focus()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Focus\VehicleFocus');
    }
}
