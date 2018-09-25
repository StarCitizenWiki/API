<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 12.08.2018
 * Time: 16:22
 */

namespace Tests\Feature\Controller\Web\Admin\StarCitizen\Vehicle\Focus;

use App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus\FocusController;
use App\Models\Api\StarCitizen\Vehicle\Focus\Focus;
use App\Models\Api\StarCitizen\Vehicle\Focus\FocusTranslation;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Controller\Web\Admin\StarCitizen\StarCitizenTestCase;

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
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus\FocusController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.starcitizen.vehicles.foci.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.starcitizen.vehicles.foci.index')
                ->assertDontSee(__('Keine Übersetzungen vorhanden'))
                ->assertSee(__('Fahrzeugfokusse'))
                ->assertSee(__('en_EN'));
        }
    }


    /**
     * Edit Tests
     */

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus\FocusController::edit
     */
    public function testEdit()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\Focus\Focus $vehicleFocus */
        $vehicleFocus = factory(Focus::class)->create();
        $vehicleFocus->translations()->save(factory(FocusTranslation::class)->make());

        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.starcitizen.vehicles.foci.edit', $vehicleFocus->getRouteKey())
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.starcitizen.vehicles.foci.edit')
                ->assertSee(__('Übersetzungen'))
                ->assertSee(__('Speichern'));
        }
    }

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus\FocusController::edit
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
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus\FocusController::update
     *
     * @covers \App\Http\Requests\TranslationRequest
     *
     * @covers \App\Models\System\ModelChangelog
     */
    public function testUpdate()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\Focus\Focus $vehicleFocus */
        $vehicleFocus = factory(Focus::class)->create();
        $vehicleFocus->translations()->save(factory(FocusTranslation::class)->make());

        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.starcitizen.vehicles.foci.update', $vehicleFocus->getRouteKey()),
            [
                'en_EN' => 'Vehicle Focus translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus\FocusController::update
     */
    public function testUpdateNotFound()
    {
        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.starcitizen.vehicles.foci.update', static::MODEL_ID_NOT_EXISTENT),
            [
                'en_EN' => 'Vehicle Focus translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Vehicle\Focus\FocusController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(FocusController::class)->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('middleware')->with('auth:admin');

        $reflectedClass = new \ReflectionClass(FocusController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller);
    }

    /**
     * {@inheritdoc}
     * Creates needed Vehicle Foci
     */
    protected function setUp()
    {
        parent::setUp();
        factory(Focus::class, 10)->create()->each(
            function (Focus $vehicleFocus) {
                $vehicleFocus->translations()->save(factory(FocusTranslation::class)->make());
            }
        );
    }
}
