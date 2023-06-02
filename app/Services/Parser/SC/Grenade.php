<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Grenade extends AbstractCommodityItem
{
    public function getData(): ?array
    {
        $attachDef = $this->getAttachDef();
        $grenade = $this->get('SCItemExplosiveParams', []);

        if ($attachDef === null || $grenade === null) {
            return null;
        }

        $description = $this->getDescription($attachDef);

        $data = $this->tryExtractDataFromDescription($description, [
            'Area of Effect' => 'aoe',
            'Damage Type' => 'damage_type',
        ]);

        $damage = collect(Arr::get($grenade, 'explosionParams.damage.0', []))->sum();

        return [
            'uuid' => $this->getUUID(),
            'description' => $this->cleanString(trim($data['description'] ?? $description)),

            'aoe' => $data['aoe'] ?? null,
            'damage_type' => $data['damage_type'] ?? null,
            'damage' => $damage ?? null,
        ];
    }
}
