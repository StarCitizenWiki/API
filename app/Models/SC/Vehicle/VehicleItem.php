<?php

declare(strict_types=1);

namespace App\Models\SC\Vehicle;

use App\Models\SC\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleItem extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_vehicle_items';

    protected $fillable = [
        'item_uuid',
        'grade',
        'class',
        'type',
    ];

    protected $with = [
        'item',
    ];

    public function ports(): HasMany
    {
        return $this->item->ports()
            ->where('name', 'NOT LIKE', '%access%')
            ->where('name', 'NOT LIKE', '%hud%');
    }
}
