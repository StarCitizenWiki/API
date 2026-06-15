<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MissionRewardGroup extends Model
{
    use HasFactory;

    protected $table = 'game_mission_reward_groups';

    protected $fillable = [
        'mission_data_id',
        'group_index',
        'weight',
        'award_only_to_mission_owner',
    ];

    protected $casts = [
        'group_index' => 'integer',
        'weight' => 'float',
        'award_only_to_mission_owner' => 'boolean',
    ];

    public function missionData(): BelongsTo
    {
        return $this->belongsTo(MissionData::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MissionRewardGroupItem::class, 'reward_group_id')
            ->with('itemData.item');
    }
}
