<?php

declare(strict_types=1);

namespace App\Models\Game;

use Database\Factories\Game\StarmapLocationFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StarmapLocation extends Model
{
    /** @use HasFactory<StarmapLocationFactory> */
    use HasFactory;

    use HasVersionedData;

    protected $table = 'game_starmap_locations';

    protected $fillable = [
        'uuid',
        'slug',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function data(): HasMany
    {
        return $this->hasMany(StarmapLocationData::class, 'starmap_location_id');
    }
}
