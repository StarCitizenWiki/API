<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\Food;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class FoodEffect extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_food_effects';

    protected $fillable = [
        'name',
    ];

    public function foods(): BelongsToMany
    {
        return $this->belongsToMany(
            Food::class,
            'star_citizen_unpacked_food_effect',
        );
    }
}
