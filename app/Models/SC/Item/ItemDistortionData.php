<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemDistortionData extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_item_distortion_data';

    protected $fillable = [
        'decay_delay',
        'decay_rate',
        'maximum',
        'warning_ratio',
        'overload_ratio',
        'recovery_ratio',
        'recovery_time',
    ];

    protected $casts = [
        'decay_delay' => 'double',
        'decay_rate' => 'double',
        'maximum' => 'double',
        'warning_ratio' => 'double',
        'overload_ratio' => 'double',
        'recovery_ratio' => 'double',
        'recovery_time' => 'double',
    ];
}
