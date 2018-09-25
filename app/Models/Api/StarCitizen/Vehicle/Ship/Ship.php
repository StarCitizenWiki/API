<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Ship;

use App\Models\Api\StarCitizen\Vehicle\Type\TypeTranslation;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use Illuminate\Database\Eloquent\Builder;

/**
 * Ship Model
 */
class Ship extends Vehicle
{
    protected $table = 'vehicles';

    /**
     * Adds the global Ship Scope
     */
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope(
            'type',
            function (Builder $builder) {
                // TODO Refactor to eliminate DB call?
                $type = TypeTranslation::where('translation', 'ground')->first();

                $builder->where('type_id', '!=', optional($type)->type_id);
            }
        );
    }
}
