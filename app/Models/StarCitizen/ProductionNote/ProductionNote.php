<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ProductionNote;

use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Production Note Model
 */
class ProductionNote extends HasTranslations
{
    use VehicleRelations;

    public $timestamps = false;

    protected $with = [
        'translations',
    ];

    /**
     * @return HasMany
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductionNoteTranslation::class);
    }
}
