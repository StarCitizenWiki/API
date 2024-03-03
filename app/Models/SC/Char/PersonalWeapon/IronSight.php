<?php

declare(strict_types=1);

namespace App\Models\SC\Char\PersonalWeapon;

use App\Models\SC\CommodityItem;
use App\Traits\HasDescriptionDataTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IronSight extends CommodityItem
{
    use HasFactory;
    use HasDescriptionDataTrait;

    protected $table = 'sc_item_iron_sights';

    protected $fillable = [
        'item_uuid',
        'default_range',
        'max_range',
        'range_increment',
        'auto_zeroing_time',
        'zoom_scale',
        'zoom_time_scale',
    ];

    protected $casts = [
        'default_range' => 'double',
        'max_range' => 'double',
        'range_increment' => 'double',
        'auto_zeroing_time' => 'double',
        'zoom_scale' => 'double',
        'zoom_time_scale' => 'double',
    ];

    public function getAttachmentPointAttribute()
    {
        return $this->getDescriptionDatum('Attachment Point');
    }

    public function getSizeAttribute()
    {
        return $this->getDescriptionDatum('Size');
    }

    public function getOpticTypeAttribute()
    {
        return $this->getDescriptionDatum('Type');
    }
}
