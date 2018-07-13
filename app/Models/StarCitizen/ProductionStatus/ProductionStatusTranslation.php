<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\ProductionStatus;

use Illuminate\Database\Eloquent\Model;

class ProductionStatusTranslation extends Model
{
    protected $fillable = [
        'language_id',
        'production_status_id',
        'status',
    ];

    public function productionStatus()
    {
        return $this->belongsTo('App\Models\StarCitizen\ProductionStatus\ProductionStatus');
    }
}
