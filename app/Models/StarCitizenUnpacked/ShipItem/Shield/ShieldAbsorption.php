<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem\Shield;

use App\Models\StarCitizenUnpacked\ShipItem\AbstractShipItemSpecification;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShieldAbsorption extends AbstractShipItemSpecification
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_ship_shield_absorptions';

    protected $fillable = [
        'ship_shield_id',
        'type',
        'min',
        'max',
    ];

    protected $casts = [
        'min' => 'double',
        'max' => 'double',
    ];

    public function shield(): BelongsTo
    {
        return $this->belongsTo(Shield::class, 'ship_shield_id', 'id');
    }
}
