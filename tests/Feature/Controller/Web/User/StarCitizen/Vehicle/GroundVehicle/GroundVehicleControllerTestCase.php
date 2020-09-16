<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\User\StarCitizen\Vehicle\GroundVehicle;

use App\Http\Controllers\Web\User\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController;
use App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Controller\Web\User\StarCitizen\StarCitizenTestCase;

/**
 * Admin GroundVehicle Controller Test Case
 */
class GroundVehicleControllerTestCase extends StarCitizenTestCase
{
    /**
     * Index Tests
     */

    /**
     * Test Index
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::index
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.starcitizen.vehicles.ground-vehicles.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.vehicles.ground_vehicles.index')
                ->assertDontSee(__('Keine Fahrzeuge vorhanden'))
                ->assertSee(__('Fahrzeuge'))
                ->assertSee('CIG ID')
                ->assertSee(GroundVehicle::count());
        }
    }


    /**
     * Edit Tests
     */

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::edit
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     */
    public function testEdit()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle $groundVehicle */
        $groundVehicle = factory(Vehicle::class)->state('ground_vehicle')->create();
        $groundVehicle->translations()->save(factory(VehicleTranslation::class)->make());

        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.vehicles.ground-vehicles.edit', $groundVehicle->getRouteKey())
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.vehicles.ground_vehicles.edit')
                ->assertSee(__('Fahrzeugdaten'))
                ->assertSee(__('Speichern'))
                ->assertSee('CIG ID')
                ->assertSee($groundVehicle->cig_id)
                ->assertSee($groundVehicle->name);
        }
    }

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::edit
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     *
     * @covers \App\Exceptions\Handler
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.vehicles.ground-vehicles.edit', static::MODEL_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }


    /**
     * Update Tests
     */

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::update
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     *
     * @covers \App\Http\Requests\System\TranslationRequest
     *
     * @covers \App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation
     * @covers \App\Models\System\ModelChangelog
     */
    public function testUpdate()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle $groundVehicle */
        $groundVehicle = factory(Vehicle::class)->state('ground_vehicle')->create();
        $groundVehicle->translations()->save(factory(VehicleTranslation::class)->make());

        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.vehicles.ground-vehicles.update', $groundVehicle->getRouteKey()),
            [
                'en_EN' => 'GroundVehicle translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::update
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\GroundVehicle\GroundVehicleController::show
     *
     * @covers \App\Exceptions\Handler
     */
    public function testUpdateNotFound()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\GroundVehicle\GroundVehicle $groundVehicle */
        $groundVehicle = factory(Vehicle::class)->state('ground_vehicle')->create();
        $groundVehicle->translations()->save(factory(VehicleTranslation::class)->make());

        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.vehicles.ground-vehicles.update', static::MODEL_ID_NOT_EXISTENT),
            [
                'en_EN' => 'GroundVehicle translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Account\AccountController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(GroundVehicleController::class)->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('middleware')->with('auth');

        $reflectedClass = new \ReflectionClass(GroundVehicleController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller);
    }

    /**
     * {@inheritdoc}
     * Creates needed GroundVehicles
     */
    protected function setUp(): void
    {
        parent::setUp();
        factory(Vehicle::class, 10)->state('ground_vehicle')->create()->each(
            function (Vehicle $groundVehicle) {
                $groundVehicle->translations()->save(factory(VehicleTranslation::class)->make());
            }
        );
    }
}