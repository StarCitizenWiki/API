<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

use Illuminate\Support\Arr;

final class HackingChip extends AbstractCommodityItem
{
    public function getData(): ?array
    {
        $attachDef = $this->getAttachDef();
        $chipParams = $this->get('HackingChipParams');
        $removableChipParams = $this->get('RemovableChipParams.values');

        if ($attachDef === null || ($chipParams === null && $removableChipParams === null)) {
            return null;
        }

        $removableChipParams = collect($removableChipParams ?? []);

        $durationMultiplier = $removableChipParams->first(fn ($val) => $val['name'] === 'Duration')['value'] ?? null;
        $errorChance = $removableChipParams->first(fn ($val) => $val['name'] === 'ErrorChance')['value'] ?? null;

        return [
            'uuid' => $this->getUUID(),

            'max_charges' => Arr::get($chipParams, 'maxCharges'),
            'duration_multiplier' => $durationMultiplier,
            'error_chance' => $errorChance,
        ];
    }
}
