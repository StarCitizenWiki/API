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

    public function testIndex() {
        $uuid = Item::query()->first()->uuid;
        $response = $this->get('api/v2/items');

        $response->assertOk()
            ->assertJsonStructure(['data'])
            ->assertSee($uuid);
    }

    public function testShow() {
        $uuid = Item::query()->first()->uuid;
        $response = $this->get('api/v2/items/' . $uuid);

        $response->assertOk()
            ->assertSee($uuid);
    }
}