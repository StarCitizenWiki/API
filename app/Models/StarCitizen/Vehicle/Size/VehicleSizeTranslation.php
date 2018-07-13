<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\Vehicle\Size;

use Illuminate\Database\Eloquent\Model;

class VehicleSizeTranslation extends Model
{
    protected $fillable = [
        'language_id',
        'vehicle_size_id',
        'size',
    ];

    public function size()
    {
        return $this->belongsTo('App\Models\StarCitizen\Vehicle\Size\Size');
    }
}
