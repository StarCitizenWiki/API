<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use App\Models\StarCitizenUnpacked\CharArmor\CharArmorAttachment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CharArmor implements ShouldQueue
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
    public function handle()
    {
        try {
            $weapons = new \App\Services\Parser\StarCitizenUnpacked\CharArmor\CharArmor();
        } catch (\JsonException | FileNotFoundException $e) {
            $this->fail($e->getMessage());

            return;
        }

        $weapons->getData()
            ->each(function ($armor) {

                /** @var \App\Models\StarCitizenUnpacked\CharArmor\CharArmor $armor */
                $model = \App\Models\StarCitizenUnpacked\CharArmor\CharArmor::updateOrCreate([
                    'uuid' => $armor['uuid'],
                ], [
                    'temp_resistance_min' => $armor['temp_resistance_min'],
                    'temp_resistance_max' => $armor['temp_resistance_max'],
                    'resistance_physical_multiplier' => $armor['resistance_physical_multiplier'],
                    'resistance_physical_threshold' => $armor['resistance_physical_threshold'],
                    'resistance_energy_multiplier' => $armor['resistance_energy_multiplier'],
                    'resistance_energy_threshold' => $armor['resistance_energy_threshold'],
                    'resistance_distortion_multiplier' => $armor['resistance_distortion_multiplier'],
                    'resistance_distortion_threshold' => $armor['resistance_distortion_threshold'],
                    'resistance_thermal_multiplier' => $armor['resistance_thermal_multiplier'],
                    'resistance_thermal_threshold' => $armor['resistance_thermal_threshold'],
                    'resistance_biochemical_multiplier' => $armor['resistance_biochemical_multiplier'],
                    'resistance_biochemical_threshold' => $armor['resistance_biochemical_threshold'],
                    'resistance_stun_multiplier' => $armor['resistance_stun_multiplier'],
                    'resistance_stun_threshold' => $armor['resistance_stun_threshold'],
                ]);

                $model->item()->updateOrCreate([
                    'uuid' => $armor['uuid'],
                ], [
                    'name' => $armor['name'],
                    'size' => $armor['size'],
                    'manufacturer' => $armor['manufacturer'],
                    'type' => $armor['type'],
                    'class' => $armor['class'],
                ]);

                $model->translations()->updateOrCreate([
                    'locale_code' => 'en_EN',
                ], [
                    'translation' => $armor['description'] ?? '',
                ]);

                $ids = [];

                foreach ($armor['attachments'] as $attachment) {
                    $ids[] = (CharArmorAttachment::updateOrCreate([
                        'name' => $attachment['name'],
                        'min_size' => $attachment['min_size'],
                        'max_size' => $attachment['max_size'],
                    ]))->id;
                }

                $model->attachments()->sync($ids);
            });
    }
}
