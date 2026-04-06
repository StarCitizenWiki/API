<?php

declare(strict_types=1);

namespace App\Models\Game\Resource;

use App\Models\Game\Commodity\Commodity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ResourceCommodity extends Pivot
{
    protected $table = 'game_resource_commodity';

    protected $fillable = [
        'resource_data_id',
        'commodity_id',
        'weight',
        'min_percentage',
        'max_percentage',
        'probability',
        'quality_scale',
        'curve_exponent',
    ];

    protected $casts = [
        'weight' => 'decimal:4',
        'min_percentage' => 'decimal:4',
        'max_percentage' => 'decimal:4',
        'probability' => 'decimal:4',
        'quality_scale' => 'decimal:4',
        'curve_exponent' => 'decimal:4',
    ];

    public function resourceData(): BelongsTo
    {
        return $this->belongsTo(ResourceData::class);
    }

    public function commodity(): BelongsTo
    {
        return $this->belongsTo(Commodity::class);
    }
}
