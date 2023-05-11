<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FlightController extends Model
{
    use HasFactory;

    protected $table = 'sc_item_flight_controllers';

    protected $fillable = [
        'item_uuid',
        'scm_speed',
        'max_speed',
        'pitch',
        'yaw',
        'roll',
    ];

    protected $casts = [
        'scm_speed' => 'double',
        'max_speed' => 'double',
        'pitch' => 'double',
        'yaw' => 'double',
        'roll' => 'double',
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
