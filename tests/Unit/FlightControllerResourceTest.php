<?php

declare(strict_types=1);

use App\Http\Resources\Game\ItemSpecification\FlightControllerResource;
use Illuminate\Http\Request;
use Tests\TestCase;

uses(TestCase::class);

it('maps all IFCS fields including afterburner data', function () {
    $fixturePath = base_path('storage/app/api/scunpacked-data/items/controller_flight_rsi_aurora_mr.json');
    $fixture = json_decode(file_get_contents($fixturePath), true, 512, JSON_THROW_ON_ERROR);

    $resource = new FlightControllerResource([
        'data' => [
            'stdItem' => $fixture['Item']['stdItem'],
        ],
    ]);

    $result = $resource->toArray(new Request);

    expect($result)->toMatchArray([
        'scm_speed' => 227,
        'boost_speed_forward' => 470,
        'boost_speed_backward' => 240,
        'max_speed' => 1230,
        'max_speed_precision_mode_full_proximity_assist' => 10,
        'max_speed_precision_mode_zero_proximity_assist' => 30,
        'torque_distance_threshold' => 0.5,
        'torque_imbalance_multiplier' => 0.3,
        'refresh_caches_on_landing_mode' => 0,
        'lift_multiplier' => 7,
        'drag_multiplier' => 5,
        'precision_min_distance' => 5,
        'precision_max_distance' => 50,
        'precision_landing_multiplier' => 0.7,
        'linear_accel_decay' => 6,
        'angular_accel_decay' => 12,
        'scm_max_drag_multiplier' => 4,
        'pitch' => 59,
        'yaw' => 51,
        'roll' => 137,
        'afterburner' => [
            'pre_delay_time' => 0,
            'ramp_up_time' => 0.6,
            'ramp_down_time' => 0.2,
            'capacitor_threshold_ratio' => 0.1,
            'capacitor_max' => 20,
            'capacitor_afterburner_idle_cost' => 1,
            'capacitor_afterburner_linear_cost' => 0,
            'capacitor_afterburner_angular_cost' => 0,
            'capacitor_regen_delay_after_use' => 0.2,
            'capacitor_regen_per_sec' => 0.75,
        ],
        'recall_params' => [
            'hover_height_at_destination' => 30,
            'forward_offset' => 20,
            'obstruction_detection_range' => 1.2,
            'default_platform_detection_range' => 50,
            'minimum_recall_distance' => 400,
            'braking_distance_offset' => 30,
        ],
        'collision_detection' => [
            'collision_warn_speed' => 6,
            'collision_warn_time' => 4,
            'collision_danger_close_warn_time' => 2,
        ],
    ]);
});
