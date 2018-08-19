<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 12.08.2018
 * Time: 16:22
 */

namespace Tests\Feature\Controller\Admin\StarCitizen\Vehicle\Type;

use App\Models\Api\StarCitizen\Vehicle\Type\VehicleType;
use App\Models\Api\StarCitizen\Vehicle\Type\VehicleTypeTranslation;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Controller\Admin\StarCitizen\StarCitizenTestCase;

/**
 * Admin Vehicle Type Controller Test Case
 */
class TypeControllerTestCase extends StarCitizenTestCase
{
    /**
     * Index Tests
     */

    /**
     * Test Index
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Type\VehicleTypeController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.starcitizen.vehicles.types.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertDontSee(__('Keine Übersetzungen vorhanden'))
                ->assertSee(__('Fahrzeugtypen'))
                ->assertSee(__('en_EN'));
        }
    }


    /**
     * Edit Tests
     */

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Type\VehicleTypeController::edit
     */
    public function testEdit()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\Type\VehicleType $vehicleType */
        $vehicleType = factory(VehicleType::class)->create();
        $vehicleType->translations()->save(factory(VehicleTypeTranslation::class)->make());

        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.starcitizen.vehicles.types.edit', $vehicleType->getRouteKey())
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertSee(__('Übersetzungen'))->assertSee(__('Speichern'));
        }
    }

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Type\VehicleTypeController::edit
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.starcitizen.vehicles.types.edit', static::MODEL_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }


    /**
     * Update Tests
     */

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Type\VehicleTypeController::update
     *
     * @covers \App\Http\Requests\TranslationRequest
     *
     * @covers \App\Models\Api\ModelChangelog
     */
    public function testUpdate()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\Type\VehicleType $vehicleType */
        $vehicleType = factory(VehicleType::class)->create();
        $vehicleType->translations()->save(factory(VehicleTypeTranslation::class)->make());

        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.starcitizen.vehicles.types.update', $vehicleType->getRouteKey()),
            [
                'en_EN' => 'Vehicle Type translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Type\VehicleTypeController::update
     */
    public function testUpdateNotFound()
    {
        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.starcitizen.vehicles.types.update', static::MODEL_ID_NOT_EXISTENT),
            [
                'en_EN' => 'Vehicle Type translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }

    /**
     * {@inheritdoc}
     * Creates needed Vehicle types
     */
    protected function setUp()
    {
        parent::setUp();
        factory(VehicleType::class, 10)->create()->each(
            function (VehicleType $vehicleType) {
                $vehicleType->translations()->save(factory(VehicleTypeTranslation::class)->make());
            }
        );
    }
}