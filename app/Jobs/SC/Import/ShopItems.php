<?php

declare(strict_types=1);

namespace App\Jobs\SC\Import;

use App\Models\SC\Item\Item;
use App\Models\SC\Shop\Shop;
use App\Models\SC\Shop\ShopItemRental;
use App\Services\Parser\SC\Manufacturers;
use App\Services\Parser\SC\Shops\Shops;
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
        $this->manufacturers = (new Manufacturers())->getData();

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
                    'version' => config('api.sc_data_version'),
                ]);

                $toSync = $shop['inventory']
                    //->unique('uuid')
                    ->mapWithKeys(function ($inventory) use ($shopModel) {
                        /** @var Item $itemModel */
                        $itemModel = Item::query()->where('uuid', $inventory['uuid'])->first();

                        if ($itemModel === null) {
                            return ['unknown' => null];
                        }

                        // TODO: Extract
                        if ($inventory['rentable'] === true && isset($inventory['rental']) && !empty($inventory['rental'])) {
                            ShopItemRental::updateOrCreate([
                                'item_uuid' => $itemModel->uuid,
                                'shop_uuid' => $shopModel->uuid,
                                'node_uuid' => $inventory['node_uuid'],
                            ], $inventory['rental'] + ['version' => config('api.sc_data_version'),]);
                        }

                        return [
                            $itemModel->id => [
                                'item_uuid' => $itemModel->uuid,
                                'shop_uuid' => $shopModel->uuid,
                                'node_uuid' => $inventory['node_uuid'],
                                'base_price' => round($inventory['base_price'], 10),
                                'base_price_offset' => $inventory['base_price_offset'],
                                'max_discount' => $inventory['max_discount'],
                                'max_premium' => $inventory['max_premium'],
                                'inventory' => $inventory['inventory'],
                                'optimal_inventory' => $inventory['optimal_inventory'],
                                'max_inventory' => $inventory['max_inventory'],
                                'auto_restock' => $inventory['auto_restock'],
                                'auto_consume' => $inventory['auto_consume'],
                                'refresh_rate' => round($inventory['refresh_rate'], 10),
                                'buyable' => $inventory['buyable'],
                                'sellable' => $inventory['sellable'],
                                'rentable' => $inventory['rentable'],
                                'version' => config('api.sc_data_version'),
                            ]
                        ];
                    })
                    ->filter(function ($item) {
                        return $item !== null;
                    });

                $shopModel->items()->sync($toSync);
            });
    }
}
