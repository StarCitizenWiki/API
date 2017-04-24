<?php
namespace App\Transformers\StarCitizenDB\Ships;

use App\Jobs\SplitShipFiles;
use App\Traits\FiltersDataTrait;
use League\Fractal\TransformerAbstract;

/**
 * Class ShipTransformer
 * @package App\Transformers\StarCitizenDB
 */
class ShipsTransformer extends TransformerAbstract
{
    use FiltersDataTrait;

    private $totalHitPoints = 0;

    /**
     * @param array $data
     *
     * @return array
     */
    public function transform(array $data) : array
    {
        $name = $data['processedName'];

        $manufacturerID = explode('_', $name)[0];

        $collectedData = [
            'name' => $name,
            //'processedName' => $name,
            'manufacturer' => [
                'name' => $data['@manufacturer'] ?? '',
                'id' => $manufacturerID,
            ],
            'description' => $data['@description'] ?? '',
            'stats' => [
                'size' => $data['@size'] ?? '',
                'status' => snake_case($data['@requiredItemTags'] ?? ''),
                'total_hit_points' => null,
            ],
            'mass' => [
                'mass_empty' => $data['ifcs']['mass_hull'] ?? $data['Parts']['Part']['@mass'] ?? '',
                'mass_items' => $data['ifcs']['mass_items'] ?? '',
                'mass_total' => $data['ifcs']['mass_total'] ?? '',
            ],
            'velocity' => [
                'precision' => $data['ifcs']['@PREVelocity'] ?? '',
                'scm' => $data['ifcs']['@SCMVelocity'] ?? '',
                'boost' => $data['ifcs']['@ABMVelocity'] ?? '',
                'jerk' => [
                    'forward' => $data['ifcs']['jerk-forward'] ?? '',
                    'back' => $data['ifcs']['jerk-back'] ?? '',
                    'up' => $data['ifcs']['jerk-up'] ?? '',
                    'down' => $data['ifcs']['jerk-down'] ?? '',
                    'lateral' => $data['ifcs']['jerk-lateral'] ?? '',
                ],
                'time_to_cruise' => $data['ifcs']['cruise_time'] ?? '',
                'stop_time' => [
                    'forward' => $data['ifcs']['stop time-forward'] ?? '',
                    'back' => $data['ifcs']['stop time-back'] ?? '',
                    'up' => $data['ifcs']['stop time-up'] ?? '',
                    'down' => $data['ifcs']['stop time-down'] ?? '',
                    'lateral' => $data['ifcs']['stop time-lateral'] ?? '',
                ],
            ],
            'rotation' => [
                'max_speed' => [
                    'pitch' => $data['ifcs']['@AngVelocity-pitch'] ?? '',
                    'yaw' => $data['ifcs']['@AngVelocity-yaw'] ?? '',
                    'roll' => $data['ifcs']['@AngVelocity-roll'] ?? '',
                ],
                'max_acceleration' => [
                    'pitch' => $data['ifcs']['@maxAngularAcceleration-pitch'] ?? '',
                    'yaw' => $data['ifcs']['@maxAngularAcceleration-yaw'] ?? '',
                    'roll' => $data['ifcs']['@maxAngularAcceleration-roll'] ?? '',
                ],
                'angular_jerk' => [
                    'pitch' => $data['ifcs']['angular jerk tuned-pitch'] ?? '',
                    'yaw' => $data['ifcs']['angular jerk tuned-yaw'] ?? '',
                    'roll' => $data['ifcs']['angular jerk tuned-roll'] ?? '',
                ],
            ],
            'thrust' => [
                'forward' => $data['ifcs']['thrust-forward'] ?? '',
                'back' => $data['ifcs']['thrust-back'] ?? '',
                'up' => $data['ifcs']['thrust-up'] ?? '',
                'down' => $data['ifcs']['thrust-down'] ?? '',
                'lateral' => $data['ifcs']['thrust-lateral'] ?? '',
                'scale' => [
                    'boost' => $data['ifcs']['boost_scale'] ?? '',
                    'ab' => $data['ifcs']['ab_scale'] ?? '',
                    'rotation' => $data['ifcs']['rotation_scale'] ?? '',
                ],
                'max_thrust' => [
                    'directional' => $data['ifcs']['@maxDirectionalThrust'] ?? '',
                    'retro' => $data['ifcs']['@maxRetroThrust'] ?? '',
                    'engine' => $data['ifcs']['@maxEngineThrust'] ?? '',
                ],
                'engine' => [
                    'max_power' => $data['ifcs']['@enginePowerMax'] ?? '',
                    'warmup_delay' => $data['ifcs']['@engineWarmupDelay'] ?? '',
                    'ignition_time' => $data['ifcs']['@engineIgnitionTime'] ?? '',
                ],
            ],
            'linear' => [
                'scale' => [
                    'forward' => $data['ifcs']['linear scale-forward'] ?? '',
                    'back' => $data['ifcs']['linear scale-back'] ?? '',
                    'up' => $data['ifcs']['linear scale-up'] ?? '',
                    'down' => $data['ifcs']['linear scale-down'] ?? '',
                    'lateral' => $data['ifcs']['linear scale-lateral'] ?? '',
                ],
                'jerk' => [
                    'scale' => [
                        'forward' => $data['ifcs']['jerk scale-forward'] ?? '',
                        'back' => $data['ifcs']['jerk scale-back'] ?? '',
                        'up' => $data['ifcs']['jerk scale-up'] ?? '',
                        'down' => $data['ifcs']['jerk scale-down'] ?? '',
                        'lateral' => $data['ifcs']['jerk scale-lateral'] ?? '',
                    ],
                ],
            ],
            'angular' => [
                'thrust' => [
                    'positive' => [
                        'pitch' => $data['ifcs']['angular thrust pos-pitch'] ?? '',
                        'yaw' => $data['ifcs']['angular thrust pos-yaw'] ?? '',
                        'roll' => $data['ifcs']['angular thrust pos-roll'] ?? '',
                    ],
                    'negative' => [
                        'pitch' => $data['ifcs']['angular thrust neg-pitch'] ?? '',
                        'yaw' => $data['ifcs']['angular thrust neg-yaw'] ?? '',
                        'roll' => $data['ifcs']['angular thrust neg-roll'] ?? '',
                    ],
                ],
                'scale' => [
                    'positive' => [
                        'pitch' => $data['ifcs']['angular scale pos-pitch'] ?? '',
                        'yaw' => $data['ifcs']['angular scale pos-yaw'] ?? '',
                        'roll' => $data['ifcs']['angular scale pos-roll'] ?? '',
                    ],
                    'negative' => [
                        'pitch' => $data['ifcs']['angular scale neg-pitch'] ?? '',
                        'yaw' => $data['ifcs']['angular scale neg-yaw'] ?? '',
                        'roll' => $data['ifcs']['angular scale neg-roll'] ?? '',
                    ],
                ],
                'jerk' => [
                    'pitch' => $data['ifcs']['angular jerk-pitch'] ?? '',
                    'yaw' => $data['ifcs']['angular jerk-yaw'] ?? '',
                    'roll' => $data['ifcs']['angular jerk-roll'] ?? '',
                ],
            ],
        ];

        $this->totalHitPoints = 0;

        array_walk_recursive($data, function ($value, $key) {
            if ($key == '@damageMax') {
                $this->totalHitPoints += $value;
            }
        });

        $collectedData['stats']['total_hit_points'] = (String) $this->totalHitPoints;

        return $collectedData;
    }
}
