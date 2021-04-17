<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked\Shops;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

final class Shops
{
    private Collection $shops;
    private Collection $mapped;

    /**
     * AssaultRifle constructor.
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct()
    {
        $items = File::get(storage_path(sprintf('app/api/scunpacked/api/dist/json/shops.json')));
        $this->shops = collect(json_decode($items, true, 512, JSON_THROW_ON_ERROR));
        $this->mapped = collect();
    }

    public function getData(): Collection
    {
        $this->shops
            ->filter(function (array $shop) {
                return isset($shop['name']) && strpos($shop['name'], ',') !== false;
            })
            ->filter(function (array $shop) {
                return isset($shop['inventory']);
            })
            ->each(function (array $shop) {
                $inventory = collect($shop['inventory'])
                    ->filter(function (array $inventory) {
                        return isset($inventory['displayName']);
                    })
                    ->map(function (array $inventory) use ($shop) {
                        return Inventory::map($inventory, $shop['name']);
                    });

                $this->mapped->put($shop['name'], $inventory);
            });

        return $this->mapped;
    }
}
