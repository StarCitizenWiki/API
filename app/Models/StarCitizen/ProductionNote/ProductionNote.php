<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\ProductionNote;

use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Model;

/**
 * Production Note Model
 */
class ProductionNote extends Model
{
    use VehicleRelations;

    protected $with = [
        'translations',
    ];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany('App\Models\StarCitizen\ProductionNote\ProductionNoteTranslation');
    }
}
