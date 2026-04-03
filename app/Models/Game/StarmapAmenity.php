<?php

declare(strict_types=1);

namespace App\Models\Game;

use Database\Factories\Game\StarmapAmenityFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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

    public function locationData(): BelongsToMany
    {
        return $this->belongsToMany(
            StarmapLocationData::class,
            'game_starmap_location_data_amenity',
            'amenity_id',
            'location_data_id'
        );
    }
}
