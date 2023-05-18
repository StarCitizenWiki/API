<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\Char\PersonalWeapon\PersonalWeaponMagazine;
use App\Services\Parser\StarCitizenUnpacked\Labels;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use JsonException;

class WeaponAttachment implements ShouldQueue
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
            $parser = new \App\Services\Parser\StarCitizenUnpacked\WeaponAttachment($this->filePath, $labels);
        } catch (FileNotFoundException | JsonException $e) {
            $this->fail($e);
            return;
        }

        $item = $parser->getData();

        if ($item === null) {
            return;
        }

        if (!empty($item['ammo'])) {
            PersonalWeaponMagazine::updateOrCreate([
                'item_uuid' => $item['uuid'],
            ], [
                'initial_ammo_count' => $item['ammo']['initial_ammo_count'] ?? null,
                'max_ammo_count' => $item['ammo']['max_ammo_count'] ?? null,
                'type' => $item['item_type'] ?? null,
            ]);
        }
    }
}
