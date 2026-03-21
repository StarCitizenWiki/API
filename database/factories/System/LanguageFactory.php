<?php

declare(strict_types=1);

namespace Database\Factories\System;

use App\Models\System\Language;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Language>
 */
class LanguageFactory extends Factory
{
    protected $model = Language::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->randomElement([
                Language::ENGLISH,
                Language::GERMAN,
                Language::CHINESE,
            ]),
        ];
    }
}
