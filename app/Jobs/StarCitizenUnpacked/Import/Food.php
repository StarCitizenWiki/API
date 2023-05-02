<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use App\Models\StarCitizenUnpacked\Food\FoodEffect;
use App\Models\StarCitizenUnpacked\Item;
use App\Services\Parser\StarCitizenUnpacked\Labels;
use App\Services\Parser\StarCitizenUnpacked\Manufacturers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
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
            $parser = new \App\Services\Parser\StarCitizenUnpacked\Food($this->filePath, $labels);
        } catch (FileNotFoundException | JsonException $e) {
            $this->fail($e);
            return;
        }
        $item = $parser->getData();

        /** @var \App\Models\StarCitizenUnpacked\Food\Food $model */
        $model = \App\Models\StarCitizenUnpacked\Food\Food::updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'nutritional_density_rating' => $item['nutritional_density_rating'] ?? null,
            'hydration_efficacy_index' => $item['hydration_efficacy_index'] ?? null,
            'container_type' => $item['container_type'] ?? null,
            'one_shot_consume' => $item['one_shot_consume'] ?? null,
            'can_be_reclosed' => $item['can_be_reclosed'] ?? null,
            'discard_when_consumed' => $item['discard_when_consumed'] ?? null,
            'version' => config('api.sc_data_version'),
        ]);

        $model->translations()->updateOrCreate([
            'locale_code' => 'en_EN',
        ], [
            'translation' => $item['description'] ?? '',
        ]);

        $ids = collect($item['effects'])->map(function (string $effect) {
            return FoodEffect::query()->firstOrCreate([
                'name' => $effect,
            ])->id;
        });

        $model->effects()->sync($ids);
    }
}
