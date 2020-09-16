<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\ProductionStatus;

use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Production Status Model
 */
class ProductionStatus extends HasTranslations
{
    use VehicleRelations;

    /**
     * @var bool
     */
    public $timestamps = false;

    /**
     * @var array
     */
    protected $fillable = [
        'slug',
    ];

    /**
     * @var array
     */
    protected $with = [
        'translations',
    ];

    /**
     * {@inheritdoc}
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * @return HasMany
     */
    public function translations()
    {
        return $this->hasMany(ProductionStatusTranslation::class);
    }
}
