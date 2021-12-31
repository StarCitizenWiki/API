<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Traits\HasBaseVersionsTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Clothing extends CommodityItem
{
    use HasFactory;
    use HasBaseVersionsTrait;

    protected $table = 'star_citizen_unpacked_clothing';

    protected $fillable = [
        'uuid',
        'type',
        'carrying_capacity',
        'temp_resistance_min',
        'temp_resistance_max',
        'version',
    ];

    protected $casts = [
        'temp_resistance_min' => 'double',
        'temp_resistance_max' => 'double',
    ];

    public function getRouteKey()
    {
        return $this->uuid;
    }
}
