<?php

declare(strict_types=1);

namespace App\Services\Parser\SC\VehicleItems;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

final class FlightController extends AbstractItemSpecification
{
    public static function getData(Collection $item): ?array
    {
        $data = self::get($item, 'IFCSParams');

        if ($data === null) {
            return null;
        }

        return array_filter([
            'max_speed' => Arr::get($data, 'maxSpeed'),
            'scm_speed' => Arr::get($data, 'scmSpeed'),
            'pitch' => Arr::get($data, 'maxAngularVelocity.x'),
            'roll' => Arr::get($data, 'maxAngularVelocity.y'),
            'yaw' => Arr::get($data, 'maxAngularVelocity.z'),

            'scm_boost_forward' => Arr::get($data, 'boostSpeedForward'),
            'scm_boost_backward' => Arr::get($data, 'boostSpeedBackward'),
            'pitch_boost_multiplier' => Arr::get($data, 'afterburner.afterburnAngVelocityMultiplier.x'),
            'roll_boost_multiplier' => Arr::get($data, 'afterburner.afterburnAngVelocityMultiplier.y'),
            'yaw_boost_multiplier' => Arr::get($data, 'afterburner.afterburnAngVelocityMultiplier.z'),
            'afterburner_capacitor' => Arr::get($data, 'afterburner.capacitorMax'),
            'afterburner_idle_cost' => Arr::get($data, 'afterburner.capacitorAfterburnerIdleCost'),
            'afterburner_linear_cost' => Arr::get($data, 'afterburner.capacitorAfterburnerLinearCost'),
            'afterburner_angular_cost' => Arr::get($data, 'afterburner.capacitorAfterburnerAngularCost'),
            'afterburner_regen_per_sec' => Arr::get($data, 'afterburner.capacitorRegenPerSec'),
            'afterburner_regen_delay_after_use' => Arr::get($data, 'afterburner.capacitorRegenDelayAfterUse'),
            'afterburner_pre_delay_time' => Arr::get($data, 'afterburner.afterburnerPreDelayTime'),
            'afterburner_ramp_up_time' => Arr::get($data, 'afterburner.afterburnerRampUpTime'),
            'afterburner_ramp_down_time' => Arr::get($data, 'afterburner.afterburnerRampDownTime'),
        ], static function ($entry) {
            return $entry !== null;
        });
    }
}
