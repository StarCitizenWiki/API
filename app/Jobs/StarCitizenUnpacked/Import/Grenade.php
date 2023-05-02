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

class Grenade implements ShouldQueue
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
            $parser = new \App\Services\Parser\StarCitizenUnpacked\Grenade($this->filePath, $labels);
        } catch (FileNotFoundException | JsonException $e) {
            $this->fail($e);
            return;
        }
        $item = $parser->getData();

        /** @var \App\Models\StarCitizenUnpacked\Grenade $model */
        $model = \App\Models\StarCitizenUnpacked\Grenade::updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'aoe' => $item['aoe'] ?? null,
            'damage_type' => $item['damage_type'] ?? null,
            'damage' => $item['damage'] ?? null,
            'version' => config('api.sc_data_version'),
        ]);

        $model->translations()->updateOrCreate([
            'locale_code' => 'en_EN',
        ], [
            'translation' => $item['description'] ?? '',
        ]);
    }
}
