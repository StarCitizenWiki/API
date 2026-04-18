<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MissionStarmapLocation extends Pivot
{
    protected $table = 'game_mission_data_starmap_location';

    protected $fillable = [
        'mission_data_id',
        'starmap_location_data_id',
        'source',
        'pool_key',
        'pool_purpose',
    ];

    public function missionData(): BelongsTo
    {
        return $this->belongsTo(MissionData::class);
    }

    public function starmapLocationData(): BelongsTo
    {
        return $this->belongsTo(StarmapLocationData::class);
    }
}
