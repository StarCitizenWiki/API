<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Size;

use Illuminate\Database\Eloquent\Model;

class VehicleSizeTranslation extends Model
{
    public function size()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Size\Size');
    }
}
