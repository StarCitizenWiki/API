<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\Food;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Food extends CommodityItem
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_foods';

    protected $fillable = [
        'uuid',
        'nutritional_density_rating',
        'hydration_efficacy_index',
        'container_type',
        'one_shot_consume',
        'can_be_reclosed',
        'discard_when_consumed',
        'occupancy_volume',
        'version',
    ];

    protected $casts = [
        'nutritional_density_rating' => 'int',
        'hydration_efficacy_index' => 'int',
        'one_shot_consume' => 'bool',
        'can_be_reclosed' => 'bool',
        'discard_when_consumed' => 'bool',
        'occupancy_volume' => 'int',
    ];

    public function getRouteKey()
    {
        return $this->uuid;
    }

    public function effects(): BelongsToMany
    {
        return $this->belongsToMany(
            FoodEffect::class,
            'star_citizen_unpacked_food_effect',
        );
    }
}
