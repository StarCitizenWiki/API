<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Food extends AbstractCommodityItem
{
    public function getData(): ?array
    {
        $attachDef = $this->getAttachDef();
        $consumable = $this->get('SCItemConsumableParams', []);

        if ($attachDef === null) {
            return null;
        }

        $description = $this->getDescription($attachDef);

        $data = $this->tryExtractDataFromDescription($description, [
            'NDR' => 'nutritional_density_rating',
            'HEI' => 'hydration_efficacy_index',
            'Effects' => 'effects',
            'Effect' => 'effect',
        ]);

        if (empty($consumable['containerTypeTag'])) {
            $consumable['containerTypeTag'] = null;
        }

        $effects = array_filter(array_map([$this, 'cleanString'], explode(',', $data['effects'] ?? '')));
        $effect = array_filter(array_map([$this, 'cleanString'], explode(',', $data['effect'] ?? '')));

        return [
            'uuid' => $this->getUUID(),
            'description_key' => $this->getDescriptionKey($attachDef),
            'description' => $this->cleanString(trim($data['description'] ?? $description)),

            'nutritional_density_rating' => $data['nutritional_density_rating'] ?? null,
            'hydration_efficacy_index' => $data['hydration_efficacy_index'] ?? null,
            'effects' => array_merge($effects, $effect),
            'type' => $attachDef['Type'],
            'container_type' => $consumable['containerTypeTag'] ?? null,
            'one_shot_consume' => $consumable['oneShotConsume'] ?? null,
            'can_be_reclosed' => $consumable['canBeReclosed'] ?? null,
            'discard_when_consumed' => $consumable['discardWhenConsumed'] ?? null,
        ];
    }
}
