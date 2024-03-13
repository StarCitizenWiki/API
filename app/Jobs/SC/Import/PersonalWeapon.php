<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\Char\PersonalWeapon\PersonalWeapon as PersonalWeaponModel;
use App\Services\Parser\SC\Labels;
use App\Services\Parser\SC\Weapon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
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
     */
    public function handle(): void
    {
        $labels = (new Labels())->getData();

        try {
            $parser = new Weapon($this->filePath, $labels);
        } catch (JsonException|FileNotFoundException $e) {
            $this->fail($e->getMessage());

            return;
        }

        $item = $parser->getData();

        try {
            $itemModel = PersonalWeaponModel::where('uuid', $item['uuid'])->firstOrFail();
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

        (new \App\Jobs\SC\Import\Ammunition($data))->handle();
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
                    'rounds_per_minute' => $mode['rounds_per_minute'],
                    'ammo_per_shot' => $mode['ammo_per_shot'],
                    'pellets_per_shot' => $mode['pellets_per_shot'],
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
