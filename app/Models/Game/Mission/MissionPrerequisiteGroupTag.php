<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionPrerequisiteGroupTag extends Model
{
    protected $table = 'game_mission_data_prerequisite_group_tag';

    protected $fillable = [
        'prerequisite_group_id',
        'type',
        'tag_uuid',
        'tag_name',
    ];

    public function prerequisiteGroup(): BelongsTo
    {
        return $this->belongsTo(MissionPrerequisiteGroup::class, 'prerequisite_group_id');
    }
}
