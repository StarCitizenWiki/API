<?php

namespace Tests\Feature\Api;

use App\Models\SC\Item\Item;
use App\Models\SC\Vehicle\Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ItemApiTest extends TestCase
{
    public function refreshDatabase(): void
    {

    }

    /**
     * GET each item and check that it is a 200 status
     */
    public function testItemResponses(): void
    {
        if (env('APP_NAME') === 'API_CI') {
            $this->markTestSkipped('Can only run locally');
        }

        Item::query()
            ->where('class_name', 'NOT LIKE', '%test_%')
            ->chunk(100, function (Collection $items) {
                $items->each(function (Item $item) {
                    $response = Http::get(route('api.v2.items.show', $item), [
                        'include' => 'variants,shops.items',
                    ]);

                    if ($response->status() !== 200) {
                        dd($response->status(), $response->body(), $item->uuid);
                    }

                    $this->assertSame(200, $response->status());
                });
            });
    }

    /**
     * GET each vehicle and check that it is a 200 status
     */
    public function testVehicleResponses(): void
    {
        if (env('APP_NAME') === 'API_CI') {
            $this->markTestSkipped('Can only run locally');
        }

        Vehicle::query()
            ->withoutEagerLoads()
            ->chunk(100, function (Collection $vehicles) {
                $vehicles->each(function (Vehicle $vehicle) {
                    $response = Http::get(route('api.v2.vehicles.show', $vehicle), [
                        'include' => 'components,hardpoints',
                    ]);

                    if ($response->status() !== 200) {
                        dd($response->status(), $response->body(), $vehicle->item_uuid);
                    }

                    $this->assertSame(200, $response->status());
                });
            });
    }
}
