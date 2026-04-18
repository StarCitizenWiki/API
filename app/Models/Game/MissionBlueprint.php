<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MissionBlueprint extends Pivot
{
    protected $table = 'game_mission_data_blueprint';

    protected $fillable = [
        'mission_data_id',
        'blueprint_data_id',
        'chance',
        'pool_uuid',
        'item_uuid',
        'item_name',
    ];

    protected $casts = [
        'chance' => 'float',
    ];

    public function missionData(): BelongsTo
    {
        return $this->belongsTo(MissionData::class);
    }

    public function blueprintData(): BelongsTo
    {
        return $this->belongsTo(BlueprintData::class);
    }
}
