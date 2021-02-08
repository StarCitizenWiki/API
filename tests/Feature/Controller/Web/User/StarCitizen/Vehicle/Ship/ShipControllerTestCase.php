<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\StarCitizen\Vehicle\Ship;

use App\Http\Controllers\Web\User\StarCitizen\Vehicle\Ship\ShipController;
use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\Vehicle;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use Dingo\Api\Dispatcher;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Controller\Web\User\StarCitizen\StarCitizenTestCase;

/**
 * Admin Ship Controller Test Case
 */
class ShipControllerTestCase extends StarCitizenTestCase
{
    /**
     * Index Tests
     */

    /**
     * Test Index
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Ship\ShipController::index
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.starcitizen.vehicles.ships.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.vehicles.ships.index')
                ->assertDontSee(__('Keine Schiffe vorhanden'))
                ->assertSee(__('Raumschiffe'))
                ->assertSee('CIG ID')
                ->assertSee(Ship::count());
        }
    }


    /**
     * Edit Tests
     */

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Ship\ShipController::edit
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testEdit()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\Ship\Ship $ship */
        $ship = Vehicle::factory()->ship()->create();

        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.vehicles.ships.edit', $ship->getRouteKey())
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.vehicles.ships.edit')
                ->assertSee(__('Schiffsdaten'))
                ->assertSee(__('Übersetzungen'))
                ->assertSee(__('Beschreibung'))
                ->assertSee(__('Speichern'))
                ->assertSee('CIG ID')
                ->assertSee($ship->name)
                ->assertSee($ship->cig_id);
        }
    }

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Ship\ShipController::edit
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     *
     * @covers \App\Exceptions\Handler
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.vehicles.ships.edit', static::MODEL_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }


    /**
     * Update Tests
     */

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Ship\ShipController::update
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     *
     * @covers \App\Http\Requests\System\TranslationRequest
     *
     * @covers \App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation
     * @covers \App\Models\System\ModelChangelog
     */
    public function testUpdate()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\Ship\Ship $ship */
        $ship = Vehicle::factory()->ship()->create();

        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.vehicles.ships.update', $ship->getRouteKey()),
            [
                'en_EN' => 'GroundVehicle translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        self::assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Ship\ShipController::update
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     *
     * @covers \App\Exceptions\Handler
     */
    public function testUpdateNotFound()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\Ship\Ship $ship */
        $ship = Vehicle::factory()->ship()->create();

        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.vehicles.ships.update', static::MODEL_ID_NOT_EXISTENT),
            [
                'en_EN' => 'GroundVehicle translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        self::assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }

    /**
     * {@inheritdoc}
     * Creates needed Ships
     */
    protected function setUp(): void
    {
        parent::setUp();
        Vehicle::factory()->count(10)->ship()->create();
    }
}