<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ItemDistortionData extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_item_distortion_data';

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
}
