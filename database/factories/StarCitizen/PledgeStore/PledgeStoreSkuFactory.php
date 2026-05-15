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
            'slug' => $this->faker->slug,
            'name' => $this->faker->words(3, true),
            'title' => $this->faker->words(3, true),
            'subtitle' => 'Standalone Ships',
            'url' => '/pledge/Standalone-Ships/Test-Ship',
            'product_id' => 72,
            'public_type_code' => 'pledge_ship_flyable',
            'parent_product_slug' => 'test',
            'parent_product_name' => 'Standalone Ships',
            'is_warbond' => false,
            'is_package' => false,
            'is_vip' => false,
            'customizable' => false,
            'native_price' => $this->faker->numberBetween(500, 50000),
            'native_discounted' => null,
            'discount_description' => null,
            'stock_available' => true,
            'stock_unlimited' => true,
            'stock_back_order' => false,
            'stock_qty' => 0,
            'stock_back_order_qty' => 0,
            'stock_level' => 'high',
            'tags' => [],
            'ships' => [],
        ];
    }
}
