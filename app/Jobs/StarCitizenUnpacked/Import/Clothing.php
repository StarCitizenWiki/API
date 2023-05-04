<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use App\Models\StarCitizenUnpacked\Item;
use App\Services\Parser\StarCitizenUnpacked\Labels;
use App\Services\Parser\StarCitizenUnpacked\Manufacturers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class Clothing implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        $labels = (new Labels())->getData();
        $manufacturers = (new Manufacturers())->getData();

        $files = Storage::allFiles('api/scunpacked-data/v2/items');

        collect($files)
            ->filter(function (string $file) {
                return strpos($file, '-raw.json') !== false;
            })
            ->map(function (string $file) use ($labels, $manufacturers) {
                return (new \App\Services\Parser\StarCitizenUnpacked\Clothing($file, $labels, $manufacturers))
                    ->getData();
            })
            ->filter(function ($item) {
                return $item !== null;
            })
            ->filter(function ($item) {
                return Item::query()->where('uuid', $item['uuid'])->exists();
            })
            ->each(function ($item) {
                /** @var Clothing $item */
                $model = \App\Models\StarCitizenUnpacked\Clothing::updateOrCreate([
                    'uuid' => $item['uuid'],
                ], [
                    'type' => $item['type'],
                    'carrying_capacity' => $item['carrying_capacity'],
                    'temp_resistance_min' => $item['temp_resistance_min'],
                    'temp_resistance_max' => $item['temp_resistance_max'],
                    'version' => config('api.sc_data_version'),
                ]);

                $model->translations()->updateOrCreate([
                    'locale_code' => 'en_EN',
                ], [
                    'translation' => $item['description'] ?? '',
                ]);
            });
    }
}
