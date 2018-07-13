<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Ship;

use Illuminate\Database\Eloquent\Model;

class ShipTranslation extends Model
{
    public function ship()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Ship\Ship');
    }
}
