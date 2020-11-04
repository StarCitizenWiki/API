<?php

declare(strict_types=1);

namespace Database\Factories\Account;

use App\Models\Account\User\UserGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = UserGroup::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->userName,
            'permission_level' => $this->faker->numberBetween(0, 4),
        ];
    }

    public function bureaucrat()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'name' => 'bureaucrat',
                    'permission_level' => 4,
                ];
            }
        );
    }

    public function sysop()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'name' => 'sysop',
                    'permission_level' => 3,
                ];
            }
        );
    }

    public function sichter()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'name' => 'sichter',
                    'permission_level' => 2,
                ];
            }
        );
    }

    public function mitarbeiter()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'name' => 'mitarbeiter',
                    'permission_level' => 1,
                ];
            }
        );
    }

    public function user()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'name' => 'user',
                    'permission_level' => 0,
                ];
            }
        );
    }
}

