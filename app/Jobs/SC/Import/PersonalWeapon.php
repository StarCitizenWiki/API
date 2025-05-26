<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\Char\PersonalWeapon\PersonalWeapon as PersonalWeaponModel;
use App\Services\Parser\SC\Weapon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use JsonException;

class PersonalWeapon extends AbstractItemCreationJob
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->loadLabels();
        try {
            $parser = new Weapon($this->filePath, $this->labels);
        } catch (JsonException|FileNotFoundException $e) {
            $this->fail($e->getMessage());

            return;
        }

        $item = $parser->getData();

        try {
            $itemModel = PersonalWeaponModel::query()->withoutGlobalScopes()->where('uuid', $item['uuid'])->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return;
        }

        $this->addAmmunition($item);
        $this->addModes($item, $itemModel);
        $this->addLoadout($item, $itemModel);
    }

    private function addAmmunition(array $data): void
    {
        if (empty($data['ammunition']) || empty($data['uuid'])) {
            return;
        }

        (new Ammunition($data))->handle();
    }

    private function addModes(array $data, PersonalWeaponModel $weapon): void
    {
        if (empty($data['modes'])) {
            return;
        }

        collect($data['modes'])
            ->filter(fn ($e) => isset($e['type']))
            ->each(function (array $mode) use ($weapon) {
                $weapon->modes()->updateOrCreate([
                    'mode' => $mode['mode'],
                ], [
                    'localised' => $mode['localised'],
                    'type' => $mode['type'],
                    'rounds_per_minute' => $mode['rounds_per_minute'] ?? 0,
                    'ammo_per_shot' => $mode['ammo_per_shot'] ?? 0,
                    'pellets_per_shot' => $mode['pellets_per_shot'] ?? 0,
                ]);
            });
    }

    private function addLoadout(array $data, PersonalWeaponModel $weapon): void
    {
        /** @var Collection $ports */
        $ports = $weapon->ports;
        if ($ports === null || $ports->isEmpty()) {
            return;
        }

        collect($data['attachments'])->each(function (array $attachment) use ($ports) {
            $port = $ports->where('name', $attachment['port'])->first();
            $port?->update([
                'equipped_item_uuid' => $attachment['uuid'],
            ]);
        });
    }
}
