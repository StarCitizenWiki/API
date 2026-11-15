<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MissionUnlockGroupMission extends Pivot
{
    use HasLinkedMissionData;

    protected $table = 'game_mission_data_unlock_group_mission';

    protected $fillable = [
        'unlock_group_id',
        'linked_mission_data_id',
    ];
}
