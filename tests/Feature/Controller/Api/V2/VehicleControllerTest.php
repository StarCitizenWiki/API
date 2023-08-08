<?php

namespace Controller\Api\V2;

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

    /**
     * @covers \App\Console\Commands\SC\ImportItems::handle
     * @covers \App\Console\Commands\SC\ImportVehicles::handle
     * @covers \App\Http\Controllers\Api\V2\SC\Vehicle\VehicleController::index
     * @covers \App\Http\Resources\SC\Vehicle\VehicleLinkResource::collection
     * @return void
     */
    public function testIndex()
    {
        $response = $this->get('api/v2/vehicles');

        $response->assertOk();
    }

    /**
     * @covers \App\Console\Commands\SC\ImportItems::handle
     * @covers \App\Console\Commands\SC\ImportVehicles::handle
     * @covers \App\Http\Controllers\Api\V2\SC\Vehicle\VehicleController::show
     * @covers \App\Http\Resources\SC\Vehicle\VehicleResource
     * @return void
     */
    public function testShow()
    {
        $uuid = Vehicle::query()->first()->uuid;
        $response = $this->get('api/v2/vehicles/' . $uuid);

        $response->assertOk()
            ->assertSee($uuid);
    }

    /**
     * @covers \App\Console\Commands\SC\ImportItems::handle
     * @covers \App\Console\Commands\SC\ImportVehicles::handle
     * @covers \App\Http\Controllers\Api\V2\SC\Vehicle\VehicleController::show
     * @covers \App\Http\Resources\SC\Vehicle\VehicleResource
     * @return void
     */
    public function testShowSpecific()
    {
        $uuid = '97648869-5fa5-42da-b804-4d9314289539';
        $response = $this->get('api/v2/vehicles/' . $uuid);

        $response->assertOk()
            ->assertSee($uuid)
            ->assertJsonStructure([
                'data' => [
                    'class_name',
                    'parts',
                ],
                'meta' => [],
            ]);
    }
}