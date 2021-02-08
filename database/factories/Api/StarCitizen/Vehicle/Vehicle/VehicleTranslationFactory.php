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

namespace Database\Factories\Api\StarCitizen\Vehicle\Vehicle;

use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use Illuminate\Database\Eloquent\Factories\Factory;

class VehicleTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = VehicleTranslation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'locale_code' => 'en_EN',
            'translation' => 'Lorem Ipsum',
        ];
    }

    public function german()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'locale_code' => 'de_DE',
                    'translation' => 'Deutsches Lorem Ipsum',
                ];
            }
        );
    }

    public function english()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'locale_code' => 'en_EN',
                    'translation' => 'Lorem Ipsum dolor sit amet',
                ];
            }
        );
    }
}
