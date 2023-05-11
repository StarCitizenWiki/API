<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification\MiningLaser;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiningLaserModifier extends Model
{
    use HasFactory;

    protected $table = 'sc_item_mining_laser_modifiers';

    protected $fillable = [
        'mining_laser_id',
        'name',
        'modifier',
    ];
}
