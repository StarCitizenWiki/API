<?php

declare(strict_types=1);

namespace App\Models\Game\Mission;

use App\Models\Game\BlueprintData;
use App\Models\Game\ItemData;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MissionBlueprint extends Pivot
{
    protected $table = 'game_mission_data_blueprint';

    protected $fillable = [
        'mission_data_id',
        'blueprint_data_id',
        'pool_uuid',
        'item_data_id',
        'chance',
    ];

    protected $casts = [
    ];

    public function mission(): BelongsTo
    {
        return $this->belongsTo(MissionData::class);
    }

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(BlueprintData::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(ItemData::class);
    }
}
