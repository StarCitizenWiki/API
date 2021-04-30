<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShipItemDistortionData extends CommodityItem
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_item_distortion_data';

    public $timestamps = false;

    protected $fillable = [
        'decay_rate',
        'maximum',
        'overload_ratio',
        'recovery_ratio',
        'recovery_time',
    ];

    protected $casts = [
        'decay_rate' => 'double',
        'maximum' => 'double',
        'overload_ratio' => 'double',
        'recovery_ratio' => 'double',
        'recovery_time' => 'double',
    ];

    public function shipItem(): BelongsTo
    {
        return $this->belongsTo(ShipItem::class);
    }
}
