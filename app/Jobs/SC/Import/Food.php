<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\Food\FoodEffect;
use App\Services\Parser\SC\Labels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;

class Food implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private string $filePath;

    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $labels = (new Labels())->getData();

        try {
            $parser = new \App\Services\Parser\SC\Food($this->filePath, $labels);
        } catch (FileNotFoundException | JsonException $e) {
            $this->fail($e);
            return;
        }
        $item = $parser->getData();

        /** @var \App\Models\SC\Food\Food $model */
        $model = \App\Models\SC\Food\Food::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'nutritional_density_rating' => $item['nutritional_density_rating'] ?? null,
            'hydration_efficacy_index' => $item['hydration_efficacy_index'] ?? null,
            'container_type' => $item['container_type'] ?? null,
            'one_shot_consume' => $item['one_shot_consume'] ?? null,
            'can_be_reclosed' => $item['can_be_reclosed'] ?? null,
            'discard_when_consumed' => $item['discard_when_consumed'] ?? null,
        ]);

        $ids = collect($item['effects'])->map(function (string $effect) {
            return (FoodEffect::firstOrCreate([
                'name' => $effect,
            ]))->id;
        });

        $model->effects()->sync($ids);
    }
}
