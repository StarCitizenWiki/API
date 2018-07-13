<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle;

use App\Traits\HasModelTranslationsTrait as HasTranslations;
use Illuminate\Database\Eloquent\Model;

class VehicleSize extends Model
{
    use HasTranslations;

    protected $with = [
        'vehicle_size_translations',
    ];

    public function ships()
    {
        return $this->hasMany('App\Models\StarCitizen\Vehicle\Ship\Ship');
    }
}
