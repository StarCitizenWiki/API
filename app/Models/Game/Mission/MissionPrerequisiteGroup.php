<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MissionPrerequisiteGroup extends Model
{
    protected $table = 'game_mission_data_prerequisite_groups';

    protected $fillable = [
        'mission_data_id',
        'group_index',
        'required_count',
    ];

    protected $casts = [
        'group_index' => 'integer',
        'required_count' => 'integer',
    ];

    public function missionData(): BelongsTo
    {
        return $this->belongsTo(MissionData::class);
    }

    public function missions(): HasMany
    {
        return $this->hasMany(MissionPrerequisiteGroupMission::class, 'prerequisite_group_id');
    }

    public function tags(): HasMany
    {
        return $this->hasMany(MissionPrerequisiteGroupTag::class, 'prerequisite_group_id');
    }
}
