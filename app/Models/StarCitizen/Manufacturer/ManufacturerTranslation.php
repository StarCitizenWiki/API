<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Manufacturer;

use Illuminate\Database\Eloquent\Model;

class ManufacturerTranslation extends Model
{
    public function manufacturer()
    {
        return $this->belongsTo('App\Models\StarCitizen\Manufacturer\Manufacturer');
    }
}
