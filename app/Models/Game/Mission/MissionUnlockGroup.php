<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MissionUnlockGroup extends Model
{
    protected $table = 'game_mission_data_unlock_groups';

    protected $fillable = [
        'mission_data_id',
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

    public function missions(): HasMany
    {
        return $this->hasMany(MissionUnlockGroupMission::class, 'unlock_group_id');
    }
}
