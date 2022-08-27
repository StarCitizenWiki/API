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
        'version',
    ];

    protected $casts = [
        'nutritional_density_rating' => 'int',
        'hydration_efficacy_index' => 'int',
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
