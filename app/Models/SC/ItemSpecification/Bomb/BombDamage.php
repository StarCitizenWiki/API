<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification\Bomb;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BombDamage extends Model
{
    use HasFactory;

    protected $table = 'sc_item_bomb_damages';

    protected $fillable = [
        'bomb_id',
        'type',
        'name',
        'damage',
    ];

    protected $casts = [
        'damage' => 'double',
    ];
}
