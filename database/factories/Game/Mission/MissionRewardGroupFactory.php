<?php

declare(strict_types=1);

namespace Database\Factories\Game\Mission;

use App\Models\Game\Mission\MissionData;
use App\Models\Game\Mission\MissionRewardGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MissionRewardGroup>
 */
class MissionRewardGroupFactory extends Factory
{
    protected $model = MissionRewardGroup::class;

    public function definition(): array
    {
        return [
            'mission_data_id' => MissionData::factory(),
            'group_index' => fake()->numberBetween(0, 3),
            'weight' => fake()->optional()->randomFloat(2, 0, 1),
            'award_only_to_mission_owner' => fake()->optional()->boolean(),
        ];
    }

    public function forMissionData(MissionData $missionData): static
    {
        return $this->state(['mission_data_id' => $missionData->id]);
    }
}
