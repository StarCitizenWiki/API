<?php

declare(strict_types=1);

namespace Database\Factories\StarCitizen\PledgeStore;

use App\Models\StarCitizen\PledgeStore\PledgeStoreSku;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PledgeStoreSku>
 */
class PledgeStoreSkuFactory extends Factory
{
    protected $model = PledgeStoreSku::class;

    public function definition(): array
    {
        return [
            'cig_id' => $this->faker->unique()->randomNumber(5),
            'name' => $this->faker->words(3, true),
            'url' => '/pledge/Standalone-Ships/Test-Ship',
            'product_id' => 72,
            'is_warbond' => false,
            'is_package' => false,
            'native_price' => $this->faker->numberBetween(500, 50000),
            'native_discounted' => null,
            'discount_description' => null,
            'stock_available' => true,
            'tags' => [],
            'data' => null,
            'images' => null,
        ];
    }
}
