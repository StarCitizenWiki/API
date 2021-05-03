<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use App\Models\StarCitizenUnpacked\Item;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal as WeaponPersonalModel;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalAmmunition;
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

                /** @var WeaponPersonalModel $model */
                $model = WeaponPersonalModel::updateOrCreate([
                    'uuid' => $weapon['uuid'],
                ], [
                    'weapon_type' => $weapon['weapon_type'] ?? null,
                    'weapon_class' => $weapon['weapon_class'] ?? null,
                    'effective_range' => $weapon['effective_range'],
                    'rof' => $weapon['rof'],
                ]);

                $model->translations()->updateOrCreate([
                    'locale_code' => 'en_EN',
                ], [
                    'translation' => $weapon['description'] ?? '',
                ]);

                $this->addAmmunition($weapon, $model);
                $this->addAttachments($weapon, $model);
                $this->addAttachmentPorts($weapon, $model);
                $this->addMagazine($weapon, $model);
                $this->addModes($weapon, $model);
            });
    }

    private function addAmmunition(array $data, WeaponPersonalModel $weapon): void
    {
        /** @var WeaponPersonalAmmunition $ammunition */
        $ammunition = $weapon->ammunition()->updateOrCreate([
            'weapon_id' => $weapon->id,
        ], [
            'size' => $data['ammunition']['size'],
            'lifetime' => $data['ammunition']['lifetime'],
            'speed' => $data['ammunition']['speed'],
            'range' => $data['ammunition']['range'],
        ]);

        collect($data['ammunition']['damages'])->each(function ($damageClass) use ($ammunition) {
            collect($damageClass)->each(function ($damage) use ($ammunition) {
                $ammunition->damages()->updateOrCreate([
                    'type' => $damage['type'],
                    'name' => $damage['name'],
                ], [
                    'damage' => $damage['damage'],
                ]);
            });
        });
    }

    private function addAttachments(array $data, WeaponPersonalModel $weapon): void
    {
        collect($data['attachments'])->each(function ($attachment) use ($weapon) {
            $weapon->attachments()->updateOrCreate([
                'position' => $attachment['position'],
            ], [
                'name' => $attachment['name'],
                'size' => $attachment['size'],
                'grade' => $attachment['grade'],
            ]);
        });
    }

    private function addAttachmentPorts(array $data, WeaponPersonalModel $weapon): void
    {
        collect($data['attachment_ports'])->each(function (array $attachment) use ($weapon) {
            $weapon->attachmentPorts()->updateOrCreate([
                'position' => $attachment['position'],
            ], [
                'name' => $attachment['name'],
                'min_size' => $attachment['min_size'],
                'max_size' => $attachment['max_size'],
            ]);
        });
    }

    private function addMagazine(array $data, WeaponPersonalModel $weapon): void
    {
        $weapon->magazine()->updateOrCreate([
            'weapon_id' => $weapon->id,
        ], [
            'initial_ammo_count' => $data['magazine']['initial_ammo_count'],
            'max_ammo_count' => $data['magazine']['max_ammo_count'],
        ]);
    }

    private function addModes(array $data, WeaponPersonalModel $weapon): void
    {
        collect($data['modes'])->each(function (array $attachment) use ($weapon) {
            $weapon->modes()->updateOrCreate([
                'mode' => $attachment['mode'],
            ], [
                'mode' => $attachment['mode'],
                'localised' => $attachment['localised'],
                'type' => $attachment['type'],
                'rounds_per_minute' => $attachment['rounds_per_minute'],
                'ammo_per_shot' => $attachment['ammo_per_shot'],
                'pellets_per_shot' => $attachment['pellets_per_shot'],
            ]);
        });
    }
}
