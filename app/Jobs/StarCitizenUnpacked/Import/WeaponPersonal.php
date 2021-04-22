<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use App\Models\StarCitizenUnpacked\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WeaponPersonal implements ShouldQueue
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
            $weapons = new \App\Services\Parser\StarCitizenUnpacked\FpsItems\WeaponPersonal();
        } catch (\JsonException | FileNotFoundException $e) {
            $this->fail($e->getMessage());

            return;
        }

        $weapons->getData(true)
            ->each(function ($weapon) {
                if (!Item::query()->where('uuid', $weapon['uuid'])->exists()) {
                    return;
                }

                /** @var \App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal $weapon */
                $model = \App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal::updateOrCreate([
                    'uuid' => $weapon['uuid'],
                ], [
                    'magazine_size' => $weapon['magazine_size'],
                    'effective_range' => $weapon['effective_range'],
                    'weapon_class' => $weapon['weapon_class'],
                    'rof' => $weapon['rof'],
                    'attachment_size_optics' => $weapon['attachments']['optics'] ?? null,
                    'attachment_size_barrel' => $weapon['attachments']['barrel'] ?? null,
                    'attachment_size_underbarrel' => $weapon['attachments']['underbarrel'] ?? null,
                    'ammunition_speed' => $weapon['ammunition']['speed'] ?? 0,
                    'ammunition_range' => $weapon['ammunition']['range'] ?? 0,
                    'ammunition_damage' => $weapon['ammunition']['damage'] ?? 0,
                ]);

                $model->item()->updateOrCreate([
                    'uuid' => $weapon['uuid'],
                ], [
                    'name' => $weapon['name'],
                    'size' => $weapon['size'],
                    'manufacturer' => $weapon['manufacturer'],
                    'type' => $weapon['type'],
                    'class' => $weapon['class'],
                ]);

                $model->translations()->updateOrCreate([
                    'locale_code' => 'en_EN',
                ], [
                    'translation' => $weapon['description'] ?? '',
                ]);

                foreach ($weapon['modes'] as $mode) {
                    $model->modes()->updateOrCreate([
                        'mode' => $mode['name'],
                    ], [
                        'rpm' => $mode['rpm'],
                        'dps' => $mode['dps'],
                    ]);
                }
            });
    }
}
