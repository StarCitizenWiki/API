<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 12.08.2018
 * Time: 16:22
 */

namespace Tests\Feature\Controller\Admin\StarCitizen\Vehicle\Ship;


use App\Models\Api\StarCitizen\Vehicle\Ship\Ship;
use App\Models\Api\StarCitizen\Vehicle\Vehicle\VehicleTranslation;
use Illuminate\Http\Response;
use Tests\Feature\Controller\Admin\StarCitizen\StarCitizenTestCase;

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
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Ship\ShipController::index
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.starcitizen.vehicles.ships.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertDontSee(__('Keine Schiffe vorhanden'))
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
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Ship\ShipController::edit
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     */
    public function testEdit()
    {
        /** @var Ship $ship */
        $ship = factory(Ship::class)->create();
        $ship->translations()->save(factory(VehicleTranslation::class)->make());

        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.starcitizen.vehicles.ships.edit', $ship->getRouteKey())
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertDontSee(__('Keine Schiffe vorhanden'))
                ->assertSee('CIG ID')
                ->assertSee($ship->cig_id)
                ->assertSee($ship->name);
        }
    }

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Ship\ShipController::edit
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Vehicle\Ship\ShipController::show
     *
     * @covers \App\Exceptions\Handler
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.starcitizen.vehicles.ships.edit', static::MODEL_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }


    /**
     * Update Tests
     */

    /**
     * Test Update
     *
     */
    public function testUpdate()
    {
        $this->markTestIncomplete();
    }

    /**
     * Test Update
     *
     */
    public function testUpdateNotFound()
    {
        $this->markTestIncomplete();
    }

    /**
     * {@inheritdoc}
     * Creates needed Ships
     */
    protected function setUp()
    {
        parent::setUp();
        factory(Ship::class, 10)->create()->each(
            function (Ship $ship) {
                $ship->translations()->save(factory(VehicleTranslation::class)->make());
            }
        );
    }
}