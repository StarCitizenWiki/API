<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class WeaponAttachment extends AbstractCommodityItem
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

        if ($attachDef === null || strpos($attachDef['Type'], 'WeaponAttachment') === false) {
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
            trim($name ?? 'Unknown Weapon Attachment')
        );

        $description = $this->labels->get(substr($attachDef['Localization']['Description'], 1)) ?? '';

        $data = $this->tryExtractDataFromDescription($description, [
            'Manufacturer' => 'manufacturer',
            'Item Type' => 'item_type',
            'Type' => 'type',
            'Attachment Point' => 'attachment_point',
            'Magnification' => 'magnification',
            'Capacity' => 'capacity',
            'Class' => 'utility_class',
        ]);

        $manufacturer = $this->manufacturers->get($attachDef['Manufacturer'], 'Unknown Manufacturer');
        if ($manufacturer === '@LOC_PLACEHOLDER') {
            $manufacturer = 'Unknown Manufacturer';
        }

        $description = str_replace(['’', '`', '´'], '\'', trim($data['description'] ?? $description));

        if ($attachDef['SubType'] === 'IronSight' && empty($data['attachment_point'])) {
            $data['attachment_point'] = 'Optic';
            $data['type'] = 'IronSight';
        }

        if (empty($data['type'])) {
            $data['type'] = $attachDef['SubType'];
        }

        if ($data['type'] === 'Magazine') {
            $data['attachment_point'] = 'Magazine Well';
        }

        return [
            'uuid' => $this->item->get('__ref'),
            'description' => trim($description),
            'name' => $name,
            'size' => $attachDef['Size'],
            'grade' => $attachDef['Grade'],
            'manufacturer' => $manufacturer,
            'type' => $data['type'] ?? null,
            'item_type' => $data['item_type']  ?? null,
            'attachment_point' => $data['attachment_point'] ?? null,
            'magnification' => $data['magnification'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'utility_class' => $data['utility_class'] ?? null,
        ];
    }
}
