<?php
/*
 * Copyright (c) 2020
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

declare(strict_types=1);

namespace Database\Factories\Account\User;

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

