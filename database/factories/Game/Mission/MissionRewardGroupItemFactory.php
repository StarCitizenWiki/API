<?php

declare(strict_types=1);

namespace Database\Factories\Game\Mission;

use App\Models\Game\ItemData;
use App\Models\Game\Mission\MissionRewardGroup;
use App\Models\Game\Mission\MissionRewardGroupItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MissionRewardGroupItem>
 */
class MissionRewardGroupItemFactory extends Factory
{
    protected $model = MissionRewardGroupItem::class;

    public function definition(): array
    {
        return [
            'reward_group_id' => MissionRewardGroup::factory(),
            'item_data_id' => ItemData::factory(),
            'amount' => fake()->optional()->numberBetween(1, 10),
            'send_to_home' => fake()->optional()->boolean(),
        ];
    }

    public function forRewardGroup(MissionRewardGroup $rewardGroup): static
    {
        return $this->state(['reward_group_id' => $rewardGroup->id]);
    }

    public function forItemData(ItemData $itemData): static
    {
        return $this->state(['item_data_id' => $itemData->id]);
    }
}
