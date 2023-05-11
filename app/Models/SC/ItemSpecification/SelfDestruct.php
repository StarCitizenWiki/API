<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\SC\Item\Item;
use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SelfDestruct extends Model
{
    use HasFactory;

    protected $table = 'sc_item_self_destrucs';

    protected $fillable = [
        'item_uuid',
        'damage',
        'radius',
        'min_radius',
        'phys_radius',
        'min_phys_radius',
        'time',
    ];

    protected $casts = [
        'damage' => 'double',
        'radius' => 'double',
        'min_radius' => 'double',
        'phys_radius' => 'double',
        'min_phys_radius' => 'double',
        'time' => 'double',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class,
            'item_uuid',
            'uuid'
        );
    }
}
