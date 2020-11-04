<?php

declare(strict_types=1);

namespace Database\Factories\Account;

use App\Models\Account\User\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        static $id = 1;

        return [
            'username' => $this->faker->userName,
            'email' => $this->faker->email,
            'blocked' => false,
            'provider' => 'starcitizenwiki',
            'provider_id' => $id++,
            'last_login' => $this->faker->dateTime,
            'api_token' => Str::random(60),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function blocked()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'blocked' => true,
                ];
            }
        );
    }
}
