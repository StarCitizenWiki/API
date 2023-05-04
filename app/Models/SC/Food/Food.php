<?php

declare(strict_types=1);

namespace App\Models\SC\Food;

use App\Models\SC\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Food extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_foods';

    protected $fillable = [
        'item_uuid',
        'nutritional_density_rating',
        'hydration_efficacy_index',
        'container_type',
        'one_shot_consume',
        'can_be_reclosed',
        'discard_when_consumed',
    ];

    protected $casts = [
        'nutritional_density_rating' => 'int',
        'hydration_efficacy_index' => 'int',
        'one_shot_consume' => 'bool',
        'can_be_reclosed' => 'bool',
        'discard_when_consumed' => 'bool',
    ];
    public function getRouteKey()
    {
        return $this->item_uuid;
    }

    public function effects(): BelongsToMany
    {
        return $this->belongsToMany(
            FoodEffect::class,
            'sc_food_effect',
        );
    }
}
