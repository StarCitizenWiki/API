<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\Item\ItemTranslation;
use App\Models\StarCitizenUnpacked\CharArmor\CharArmorAttachment;
use App\Models\StarCitizenUnpacked\Item;
use App\Services\Parser\StarCitizenUnpacked\Labels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

        /** @var \App\Models\SC\Char\Clothing\Clothing $model */
        $model = \App\Models\SC\Char\Clothing\Clothing::updateOrCreate([
            'item_uuid' => $item['uuid'],
        ], [
            'type' => $item['type'],
        ]);

        if (!empty($item['description'])) {
            $model->translations()->updateOrCreate([
                'locale_code' => 'en_EN',
            ], [
                'translation' => $item['description'],
            ]);
        }

        if (isset($item['resistances'])) {
            if (!empty($item['damage_reduction'])) {
                $model->resistances()->updateOrCreate([
                    'type' => 'damage_reduction',
                ], [
                    'multiplier' => str_replace('%', '', $item['damage_reduction']) / 100,
                ]);
            }

            foreach ($item['resistances'] as $type => $resistance) {
                $model->resistances()->updateOrCreate([
                    'type' => $type,
                ], [
                    'multiplier' => $resistance['multiplier'] ?? null,
                    'threshold' => $resistance['threshold'] ?? null,
                ]);
            }
        }
    }
}
