<?php

declare(strict_types=1);

namespace Database\Factories\Game\Mission;

use App\Models\Game\GameVersion;
use App\Models\Game\Mission\Mission;
use App\Models\Game\Mission\MissionData;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MissionData>
 */
class MissionDataFactory extends Factory
{
    protected $model = MissionData::class;

    public function definition(): array
    {
        return [
            'mission_id' => Mission::factory(),
            'game_version_id' => GameVersion::factory(),
            'debug_name' => fake()->words(3, true),
            'title' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'mission_type' => fake()->randomElement(['Bounty Hunter', 'Hauling', 'Mercenary', 'Delivery', 'Salvage', 'Investigation', 'Mining']),
            'generator_class' => null,
            'illegal' => false,
            'shareable' => false,
            'once_only' => false,
            'available_in_prison' => false,
            'not_for_release' => false,
            'work_in_progress' => false,
            'calculated_reward' => false,
            'has_combat' => false,
            'has_defend_objective' => false,
        ];
    }

    public function forVersion(GameVersion $version): static
    {
        return $this->state(['game_version_id' => $version->id]);
    }

    public function forMission(Mission $mission): static
    {
        return $this->state(['mission_id' => $mission->id]);
    }

    public function ofType(?string $type): static
    {
        return $this->state(['mission_type' => $type]);
    }

    public function withGeneratorClass(?string $generatorClass): static
    {
        return $this->state(['generator_class' => $generatorClass]);
    }

    public function withDebugName(?string $debugName): static
    {
        return $this->state(['debug_name' => $debugName]);
    }
}
