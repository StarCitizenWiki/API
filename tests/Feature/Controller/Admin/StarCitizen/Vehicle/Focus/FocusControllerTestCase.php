<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 12.08.2018
 * Time: 16:22
 */

namespace Tests\Feature\Controller\Admin\StarCitizen\Vehicle\Focus;

use App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus;
use App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocusTranslation;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Controller\Admin\StarCitizen\StarCitizenTestCase;

/**
 * Admin Vehicle Focus Controller Test Case
 */
class FocusControllerTestCase extends StarCitizenTestCase
{
    /**
     * Index Tests
     */

    /**
     * Test Index
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus\VehicleFocusController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.starcitizen.vehicles.foci.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertDontSee(__('Keine Übersetzungen vorhanden'));
        }
    }


    /**
     * Edit Tests
     */

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus\VehicleFocusController::edit
     */
    public function testEdit()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus $vehicleFocus */
        $vehicleFocus = factory(VehicleFocus::class)->create();
        $vehicleFocus->translations()->save(factory(VehicleFocusTranslation::class)->make());

        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.starcitizen.vehicles.foci.edit', $vehicleFocus->getRouteKey())
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertDontSee(__('Keine Übersetzungen vorhanden'));
        }
    }

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus\VehicleFocusController::edit
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.starcitizen.vehicles.foci.edit', static::MODEL_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }


    /**
     * Update Tests
     */

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus\VehicleFocusController::update
     *
     * @covers \App\Http\Requests\TranslationRequest
     *
     * @covers \App\Models\Api\ModelChangelog
     */
    public function testUpdate()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\Focus\VehicleFocus $vehicleFocus */
        $vehicleFocus = factory(VehicleFocus::class)->create();
        $vehicleFocus->translations()->save(factory(VehicleFocusTranslation::class)->make());

        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.starcitizen.vehicles.foci.update', $vehicleFocus->getRouteKey()),
            [
                'en_EN' => 'Vehicle Focus translation',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus\VehicleFocusController::update
     */
    public function testUpdateNotFound()
    {
        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.starcitizen.vehicles.foci.update', static::MODEL_ID_NOT_EXISTENT),
            [
                'en_EN' => 'Vehicle Focus translation',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }

    /**
     * {@inheritdoc}
     * Creates needed Vehicle Foci
     */
    protected function setUp()
    {
        parent::setUp();
        factory(VehicleFocus::class, 10)->create()->each(
            function (VehicleFocus $vehicleFocus) {
                $vehicleFocus->translations()->save(factory(VehicleFocusTranslation::class)->make());
            }
        );
    }
}
