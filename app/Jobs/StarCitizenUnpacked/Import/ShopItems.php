<?php

declare(strict_types=1);

namespace App\Jobs\StarCitizenUnpacked\Import;

use App\Models\StarCitizenUnpacked\Item;
use App\Models\StarCitizenUnpacked\Shop\Shop;
use App\Services\Parser\StarCitizenUnpacked\Shops\Inventory;
use App\Services\Parser\StarCitizenUnpacked\Shops\Shops;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ShopItems implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $shops = new Shops();
        } catch (\JsonException | FileNotFoundException $e) {
            $this->fail($e->getMessage());

            return;
        }

        $shops->getData()
            ->each(function ($shop) {
                /** @var Shop $shop */
                $shopModel = Shop::updateOrCreate([
                    'uuid' => $shop['shop']['uuid'],
                ], [
                    'name_raw' => $shop['shop']['name_raw'],
                    'name' => $shop['shop']['name'],
                    'position' => $shop['shop']['position'],
                    'profit_margin' => $shop['shop']['profit_margin'],
                ]);

                $toSync = $shop['inventory']->mapWithKeys(function ($inventory) use ($shopModel) {
                    if (in_array($inventory['type'], Inventory::UNKNOWN_TYPES, true)) {
                        $itemModel = $this->createModel($inventory);
                    } else {
                        /** @var Item $itemModel */
                        $itemModel = Item::query()->where('uuid', $inventory['uuid'])->first();
                    }

                    if ($itemModel === null) {
                        return ['unknown' => null];
                    }

                    return [
                        $itemModel->id => [
                            'item_uuid' => $itemModel->uuid,
                            'shop_uuid' => $shopModel->uuid,
                            'base_price' => round($inventory['base_price'], 10),
                            'base_price_offset' => $inventory['base_price_offset'],
                            'max_discount' => $inventory['max_discount'],
                            'max_premium' => $inventory['max_premium'],
                            'inventory' => $inventory['inventory'],
                            'optimal_inventory' => $inventory['optimal_inventory'],
                            'max_inventory' => $inventory['max_inventory'],
                            'auto_restock' => $inventory['auto_restock'],
                            'auto_consume' => $inventory['auto_consume'],
                            'refresh_rate' => $inventory['refresh_rate'],
                            'buyable' => $inventory['buyable'],
                            'sellable' => $inventory['sellable'],
                            'rentable' => $inventory['rentable'],
                        ]
                    ];
                })
                    ->filter(function ($item) {
                        return $item !== null;
                    });

                $shopModel->items()->sync($toSync);
            });
    }

    /**
     * Creates a model for minerals
     *
     * @param array $item
     * @return mixed
     */
    private function createModel(array $item)
    {
        return Item::updateOrCreate([
            'uuid' => $item['uuid'],
        ], [
            'name' => $item['name'],
            'type' => $item['type'],
            'sub_type' => $item['sub_type'],
        ]);
    }
}
