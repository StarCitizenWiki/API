<?php

declare(strict_types=1);

namespace Database\Factories\Api;

use App\Models\Api\Notification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class NotificationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'level' => $this->faker->numberBetween(0, 3),
            'content' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.',
            'order' => $this->faker->numberBetween(0, 4),
            'output_status' => true,
            'output_email' => false,
            'output_index' => false,
            'expired_at' => $this->faker->dateTime,
            'published_at' => Carbon::now(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }

    public function active()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'expired_at' => Carbon::now()->addWeek(),
                ];
            }
        );
    }

    public function expired()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'expired_at' => Carbon::now()->subDay(),
                ];
            }
        );
    }

    public function notPublished()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'published_at' => Carbon::now()->addWeek(),
                ];
            }
        );
    }

    public function email()
    {
        return $this->state(
            function (array $attributes) {
                return [
                    'output_email' => true,
                ];
            }
        );
    }
}
