<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

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

class CharArmor implements ShouldQueue
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
            $parser = new \App\Services\Parser\StarCitizenUnpacked\CharArmor($this->filePath, $labels);
        } catch (FileNotFoundException | JsonException $e) {
            $this->fail($e);
            return;
        }
        $armor = $parser->getData();

        /** @var \App\Models\StarCitizenUnpacked\CharArmor\CharArmor $model */
        $model = \App\Models\StarCitizenUnpacked\CharArmor\CharArmor::updateOrCreate([
            'uuid' => $armor['uuid'],
        ], [
            'armor_type' => $armor['type'],
            'carrying_capacity' => $armor['carrying_capacity'],
            'damage_reduction' => $armor['damage_reduction'],
            'temp_resistance_min' => $armor['temp_resistance_min'],
            'temp_resistance_max' => $armor['temp_resistance_max'],
            'version' => config('api.sc_data_version'),
        ]);

        $model->translations()->updateOrCreate([
            'locale_code' => 'en_EN',
        ], [
            'translation' => $armor['description'] ?? '',
        ]);

        $ids = [];

        if (isset($armor['resistances'])) {
            foreach ($armor['resistances'] as $type => $resistance) {
                $model->resistances()->updateOrCreate([
                    'type' => $type,
                ], [
                    'multiplier' => $resistance['multiplier'],
                    'threshold' => $resistance['threshold'] ?? 0,
                ]);
            }
        }

        $model->attachments()->sync($ids);
    }
}
