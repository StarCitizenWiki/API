<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\ShipItem;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

abstract class AbstractShipItemSpecification extends Model
{
    protected $with = [
        'shipItem'
    ];

    public function shipItem(): BelongsTo
    {
        return $this->belongsTo(ShipItem::class, 'uuid', 'uuid')
            ->where('version', config('api.sc_data_version'));
    }
}
