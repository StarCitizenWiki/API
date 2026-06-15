<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use App\Models\Game\ItemData;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissionRewardGroupItem extends Model
{
    use HasFactory;

    protected $table = 'game_mission_reward_group_item';

    protected $fillable = [
        'reward_group_id',
        'item_data_id',
        'amount',
        'send_to_home',
    ];

    protected $casts = [
        'amount' => 'integer',
        'send_to_home' => 'boolean',
    ];

    public function rewardGroup(): BelongsTo
    {
        return $this->belongsTo(MissionRewardGroup::class, 'reward_group_id');
    }

    public function itemData(): BelongsTo
    {
        return $this->belongsTo(ItemData::class, 'item_data_id')
            ->select(['game_item_data.id', 'game_item_data.item_id', 'game_item_data.name']);
    }
}
