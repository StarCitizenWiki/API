<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

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

class Clothing implements ShouldQueue
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
            $parser = new \App\Services\Parser\StarCitizenUnpacked\Clothing($this->filePath, $labels);
        } catch (FileNotFoundException | JsonException $e) {
            $this->fail($e);
            return;
        }

        $item = $parser->getData();

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
    }
}
