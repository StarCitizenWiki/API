<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MissionChain extends Pivot
{
    protected $table = 'game_mission_data_mission_chain';

    protected $fillable = [
        'mission_data_id',
        'linked_mission_data_id',
        'chain_type',
        'group_index',
        'tag_uuid',
        'tag_name',
    ];

    protected $casts = [
        'group_index' => 'integer',
    ];

    public function missionData(): BelongsTo
    {
        return $this->belongsTo(MissionData::class);
    }

    public function linkedMissionData(): BelongsTo
    {
        return $this->belongsTo(MissionData::class);
    }
}
