<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 12.08.2018
 * Time: 16:22
 */

namespace Tests\Feature\Controller\Web\User\StarCitizen\Vehicle\Size;

use App\Http\Controllers\Web\User\StarCitizen\Vehicle\Size\SizeController;
use App\Models\Api\StarCitizen\Vehicle\Size\Size;
use App\Models\Api\StarCitizen\Vehicle\Size\SizeTranslation;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Controller\Web\User\StarCitizen\StarCitizenTestCase;

/**
 * Admin Vehicle Size Controller Test Case
 */
class SizeControllerTestCase extends StarCitizenTestCase
{
    /**
     * Index Tests
     */

    /**
     * Test Index
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Size\SizeController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.starcitizen.vehicles.sizes.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.vehicles.sizes.index')
                ->assertDontSee(__('Keine Übersetzungen vorhanden'))
                ->assertSee(__('Fahrzeuggrößen'))
                ->assertSee(__('en_EN'));
        }
    }


    /**
     * Edit Tests
     */

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Size\SizeController::edit
     */
    public function testEdit()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\Size\Size $vehicleSize */
        $vehicleSize = factory(Size::class)->create();
        $vehicleSize->translations()->save(factory(SizeTranslation::class)->make());

        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.vehicles.sizes.edit', $vehicleSize->getRouteKey())
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.vehicles.sizes.edit')
                ->assertSee(__('Übersetzungen'))
                ->assertSee(__('Speichern'));
        }
    }

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Size\SizeController::edit
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.vehicles.sizes.edit', static::MODEL_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }


    /**
     * Update Tests
     */

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Size\SizeController::update
     *
     * @covers \App\Http\Requests\System\TranslationRequest
     *
     * @covers \App\Models\System\ModelChangelog
     */
    public function testUpdate()
    {
        /** @var \App\Models\Api\StarCitizen\Vehicle\Size\Size $vehicleSize */
        $vehicleSize = factory(Size::class)->create();
        $vehicleSize->translations()->save(factory(SizeTranslation::class)->make());

        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.vehicles.sizes.update', $vehicleSize->getRouteKey()),
            [
                'en_EN' => 'Vehicle Size translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Size\SizeController::update
     */
    public function testUpdateNotFound()
    {
        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.vehicles.sizes.update', static::MODEL_ID_NOT_EXISTENT),
            [
                'en_EN' => 'Vehicle Size translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Size\SizeController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(SizeController::class)->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('middleware')->with('auth');

        $reflectedClass = new \ReflectionClass(SizeController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller);
    }

    /**
     * {@inheritdoc}
     * Creates needed Vehicle sizes
     */
    protected function setUp()
    {
        parent::setUp();
        factory(Size::class, 10)->create()->each(
            function (Size $vehicleSize) {
                $vehicleSize->translations()->save(factory(SizeTranslation::class)->make());
            }
        );
    }
}
