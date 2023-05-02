<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Container extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_containers';

    protected $fillable = [
        'item_uuid',
        'width',
        'height',
        'length',
        'scu',
    ];

    protected $casts = [
        'width' => 'double',
        'height' => 'double',
        'length' => 'double',
        'scu' => 'double',
    ];
}
