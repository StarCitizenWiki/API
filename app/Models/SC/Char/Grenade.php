<?php

declare(strict_types=1);

namespace App\Models\SC\Char;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Grenade extends CommodityItem
{
    use HasFactory;

    protected $table = 'sc_grenades';

    protected $fillable = [
        'item_uuid',
        'aoe',
        'damage_type',
        'damage',
    ];

    protected $casts = [
        'aoe' => 'double',
        'damage' => 'double',
    ];

    public function getRouteKey()
    {
        return $this->item_uuid;
    }
}
