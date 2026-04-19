<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MissionPrerequisiteGroupMission extends Pivot
{
    protected $table = 'game_mission_data_prerequisite_group_mission';

    protected $fillable = [
        'prerequisite_group_id',
        'linked_mission_data_id',
    ];

    public function prerequisiteGroup(): BelongsTo
    {
        return $this->belongsTo(MissionPrerequisiteGroup::class, 'prerequisite_group_id');
    }

    public function linkedMissionData(): BelongsTo
    {
        return $this->belongsTo(MissionData::class, 'linked_mission_data_id');
    }
}
