<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MissionUnlockGroupMission extends Pivot
{
    protected $table = 'game_mission_data_unlock_group_mission';

    protected $fillable = [
        'unlock_group_id',
        'linked_mission_data_id',
    ];

    public function unlockGroup(): BelongsTo
    {
        return $this->belongsTo(MissionUnlockGroup::class, 'unlock_group_id');
    }

    public function linkedMissionData(): BelongsTo
    {
        return $this->belongsTo(MissionData::class, 'linked_mission_data_id')
            ->select([
                'game_mission_data.id',
                'game_mission_data.mission_id',
                'game_mission_data.title',
                'game_mission_data.debug_name',
                'game_mission_data.mission_type',
            ])
            ->with('mission');
    }
}
