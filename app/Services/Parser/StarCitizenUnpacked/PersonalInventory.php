<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

use App\Services\Parser\StarCitizenUnpacked\ShipItems\AbstractItemSpecification;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class PersonalInventory extends AbstractItemSpecification
{
    public static function getData(array $item, Collection $rawData): ?array
    {
        if (
            !isset(
                $rawData['Components']['SCItemPersonalInventoryParams']['containerParams'],
                $rawData['Components']['SInventoryParams']['capacity']
            )
        ) {
            return null;
        }

        try {
            $item = File::get(
                storage_path(sprintf('app/api/scunpacked-data/items/%s.json', strtolower($rawData['ClassName'])))
            );

            $item = collect(json_decode($item, true, 512, JSON_THROW_ON_ERROR));

            if (isset($item['personalInventory'])) {
                return [
                    'scu' => $item['personalInventory']['SCU'],
                ];
            }
        } catch (FileNotFoundException | JsonException $e) {
            //
        }

        $basePath = 'Components.SInventoryParams.capacity.';

        $centi = ((double)Arr::get($rawData, ($basePath . 'SCentiCargoUnit.centiSCU', 0)) * (10 ** -2);
        $micro = ((double)Arr::get($rawData, ($basePath . 'SCentiCargoUnit.microSCU', 0)) * (10 ** -6);

        $out = max($centi, $micro);

        return [
            'scu' => $out,
        ];
    }
}
