<?php

namespace Controller\Api\V2;

use App\Models\SC\Item\Item;
use App\Models\SC\Vehicle\Vehicle;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class VehicleControllerTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        $this->createSystemLanguages();
        Artisan::call('sc:import-items');
    }

    public function testIndex() {
        $response = $this->get('api/v2/vehicles');

        $response->assertOk();
    }

    public function testShow() {
        $uuid = Vehicle::query()->first()->uuid;
        $response = $this->get('api/v2/vehicles/' . $uuid);

        $response->assertOk()
            ->assertSee($uuid);
    }
}