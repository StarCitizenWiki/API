<?php

declare(strict_types=1);

namespace App\Models\SC\Food;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodEffect extends Model
{
    use HasFactory;

    protected $table = 'sc_food_effects';

    protected $fillable = [
        'name',
    ];
}
