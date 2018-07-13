<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Type;

use Illuminate\Database\Eloquent\Model;

class VehicleTypeTranslation extends Model
{
    protected $fillable = [
        'language_id',
        'vehicle_type_id',
        'type',
    ];

    public function type()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Type\Type');
    }
}
