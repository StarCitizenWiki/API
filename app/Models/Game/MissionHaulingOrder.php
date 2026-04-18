<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionHaulingOrder extends Model
{
    protected $table = 'game_mission_data_hauling_order';

    protected $fillable = [
        'mission_data_id',
        'item_kind',
        'item_uuid',
        'item_name',
        'min_amount',
        'max_amount',
        'max_container_size',
        'min_scu',
        'max_scu',
    ];

    protected $casts = [
        'min_amount' => 'integer',
        'max_amount' => 'integer',
        'max_container_size' => 'integer',
        'min_scu' => 'float',
        'max_scu' => 'float',
    ];

    public function missionData(): BelongsTo
    {
        return $this->belongsTo(MissionData::class);
    }
}
