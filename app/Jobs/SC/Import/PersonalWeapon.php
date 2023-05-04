<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\Char\PersonalWeapon\PersonalWeapon;
use App\Models\SC\Char\PersonalWeapon\PersonalWeaponAmmunition;
use App\Models\StarCitizenUnpacked\Item;
use App\Models\StarCitizenUnpacked\ItemPort;
use App\Models\StarCitizenUnpacked\WeaponPersonal\Attachment;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonal as WeaponPersonalModel;
use App\Models\StarCitizenUnpacked\WeaponPersonal\WeaponPersonalAmmunition;
use App\Services\Parser\StarCitizenUnpacked\Labels;
use App\Services\Parser\StarCitizenUnpacked\Weapon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;

class PersonalWeapon implements ShouldQueue
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
            $parser = new Weapon($this->filePath, $labels);
        } catch (JsonException | FileNotFoundException $e) {
            $this->fail($e->getMessage());
            return;
        }

        $weapon = $parser->getData();

        $model = PersonalWeapon::updateOrCreate([
            'item_uuid' => $weapon['uuid'],
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
        $this->addModes($weapon, $model);
    }

    private function addAmmunition(array $data, WeaponPersonalModel $weapon): void
    {
        if (empty($data['ammunition'])) {
            return;
        }

        /** @var PersonalWeaponAmmunition $ammunition */
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

    private function addModes(array $data, PersonalWeapon $weapon): void
    {
        if (empty($data['modes'])) {
            return;
        }

        collect($data['modes'])->each(function (array $mode) use ($weapon) {
            $weapon->modes()->updateOrCreate([
                'mode' => $mode['mode'],
            ], [
                'localised' => $mode['localised'],
                'type' => $mode['type'],
                'rounds_per_minute' => $mode['rounds_per_minute'],
                'ammo_per_shot' => $mode['ammo_per_shot'],
                'pellets_per_shot' => $mode['pellets_per_shot'],
            ]);
        });
    }
}
