<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Food extends AbstractCommodityItem
{
    private Collection $item;
    private Collection $labels;
    private Collection $manufacturers;

    /**
     * @param string $fileName
     * @param Collection $labels
     * @param Collection $manufacturers
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct(string $fileName, Collection $labels, Collection $manufacturers)
    {
        $items = File::get(storage_path(sprintf('app/%s', $fileName)));
        $this->item = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
        $this->labels = $labels;
        $this->manufacturers = $manufacturers;
    }

    public function getData(): ?array
    {
        $attachDef = $this->item->pull('Components.SAttachableComponentParams.AttachDef');
        $consumable = $this->item->pull('Components.SCItemConsumableParams', []);
        $commodity = $this->item->pull('Components.CommodityComponentParams', []);

        if ($attachDef === null || !in_array($attachDef['Type'], ['Drink', 'Food'], true)) {
            return null;
        }

        $name = $this->labels->get(substr($attachDef['Localization']['Name'], 1));
        $name = str_replace(
            [
                '“',
                '”',
                '"',
                '\'',
            ],
            '"',
            trim($name ?? 'Unknown Food')
        );

        $description = $this->labels->get(substr($attachDef['Localization']['Description'], 1)) ?? '';

        $data = $this->tryExtractDataFromDescription($description, [
            'NDR' => 'nutritional_density_rating',
            'HEI' => 'hydration_efficacy_index',
            'Effects' => 'effects',
        ]);

        $manufacturer = $this->manufacturers->get($attachDef['Manufacturer'], 'Unknown Manufacturer');
        if ($manufacturer === '@LOC_PLACEHOLDER') {
            $manufacturer = 'Unknown Manufacturer';
        }

        $description = str_replace(['’', '`', '´'], '\'', trim($data['description'] ?? $description));

        if (empty($consumable['containerTypeTag'])) {
            $consumable['containerTypeTag'] = null;
        }

        return [
            'uuid' => $this->item->get('__ref'),
            'description' => $description,
            'name' => $name,
            'manufacturer' => $manufacturer,
            'nutritional_density_rating' => $data['nutritional_density_rating'] ?? null,
            'hydration_efficacy_index' => $data['hydration_efficacy_index'] ?? null,
            'effects' => array_filter(array_map('trim', explode(',', $data['effects'] ?? ''))),
            'type' => $attachDef['Type'],
            'container_type' => $consumable['containerTypeTag'] ?? null,
            'one_shot_consume' => $consumable['oneShotConsume'] ?? null,
            'can_be_reclosed' => $consumable['canBeReclosed'] ?? null,
            'discard_when_consumed' => $consumable['discardWhenConsumed'] ?? null,
            'occupancy_volume' => $commodity['occupancy']['SMicroCargoUnit']['microSCU'] ?? null,
        ];
    }
}
