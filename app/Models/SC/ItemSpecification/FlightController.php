<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

        'scm_boost_forward',
        'scm_boost_backward',
        'pitch_boost_multiplier',
        'roll_boost_multiplier',
        'yaw_boost_multiplier',
        'afterburner_capacitor',
        'afterburner_idle_cost',
        'afterburner_linear_cost',
        'afterburner_angular_cost',
        'afterburner_regen_per_sec',
        'afterburner_regen_delay_after_use',
        'afterburner_pre_delay_time',
        'afterburner_ramp_up_time',
        'afterburner_ramp_down_time',
    ];

    protected $casts = [
        'scm_speed' => 'double',
        'max_speed' => 'double',
        'pitch' => 'double',
        'yaw' => 'double',
        'roll' => 'double',

        'scm_boost_forward' => 'double',
        'scm_boost_backward' => 'double',
        'pitch_boost_multiplier' => 'double',
        'roll_boost_multiplier' => 'double',
        'yaw_boost_multiplier' => 'double',
        'afterburner_capacitor' => 'double',
        'afterburner_idle_cost' => 'double',
        'afterburner_linear_cost' => 'double',
        'afterburner_angular_cost' => 'double',
        'afterburner_regen_per_sec' => 'double',
        'afterburner_regen_delay_after_use' => 'double',
        'afterburner_pre_delay_time' => 'double',
        'afterburner_ramp_up_time' => 'double',
        'afterburner_ramp_down_time' => 'double',
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
