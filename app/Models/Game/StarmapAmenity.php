<?php

declare(strict_types=1);

namespace App\Models\Game;

use Database\Factories\Game\StarmapAmenityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StarmapAmenity extends Model
{
    /** @use HasFactory<StarmapAmenityFactory> */
    use HasFactory;

    protected $table = 'game_starmap_amenities';

    protected $fillable = [
        'uuid',
        'name',
        'display_name',
    ];
}
