<?php

namespace Controller\Api\V2;

use App\Models\SC\Item\Item;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ItemControllerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();
        Artisan::call('sc:import-items');
    }

    /**
     * @covers \App\Console\Commands\SC\ImportItems::handle
     * @covers \App\Http\Controllers\Api\V2\SC\ItemController::index
     * @covers \App\Http\Resources\SC\Item\ItemLinkResource::collection
     * @return void
     */
    public function testIndex()
    {
        $uuid = Item::query()->first()->uuid;
        $response = $this->get('api/v2/items');

        $response->assertOk()
            ->assertJsonStructure(['data'])
            ->assertSee($uuid);
    }

    /**
     * @covers \App\Console\Commands\SC\ImportItems::handle
     * @covers \App\Http\Controllers\Api\V2\SC\ItemController::show
     * @covers \App\Http\Resources\SC\Item\ItemResource
     * @return void
     */
    public function testShow()
    {
        $uuid = Item::query()->first()->uuid;
        $response = $this->get('api/v2/items/' . $uuid);

        $response->assertOk()
            ->assertSee($uuid);
    }

    /**
     * @covers \App\Console\Commands\SC\ImportItems::handle
     * @covers \App\Http\Controllers\Api\V2\SC\ItemController::show
     * @covers \App\Http\Resources\SC\Item\ItemResource
     * @return void
     */
    public function testShowSpecific()
    {
        $uuid = '9c478af1-acfd-4e88-b065-c5ebeb05f507';
        $response = $this->get('api/v2/items/' . $uuid);

        $response->assertOk()
            ->assertSee($uuid)
            ->assertJsonStructure([
                'data' => [
                    'clothing' => [
                        'clothing_type',
                    ],
                ],
            ]);
    }
}