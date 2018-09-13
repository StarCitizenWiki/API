<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 11.08.2018
 * Time: 22:00
 */

namespace Tests\Feature\Controller\Admin\StarCitizen\Manufacturer;

use App\Models\Api\StarCitizen\Manufacturer\Manufacturer;
use App\Models\Api\StarCitizen\Manufacturer\ManufacturerTranslation;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Controller\Admin\StarCitizen\StarCitizenTestCase;

/**
 * Manufacturer Controller Test Case
 */
class ManufacturerControllerTestCase extends StarCitizenTestCase
{
    /**
     * Index Tests
     */

    /**
     * Test Index
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Manufacturer\ManufacturerController::index
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.starcitizen.manufacturers.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.starcitizen.manufacturers.index')
                ->assertDontSee(__('Keine Hersteller vorhanden'))
                ->assertSee(__('Hersteller'))
                ->assertSee(__('Name'))
                ->assertSee('CIG ID')
                ->assertSee(Manufacturer::count());
        }
    }


    /**
     * Edit Tests
     */

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Manufacturer\ManufacturerController::edit
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     */
    public function testEdit()
    {
        /** @var Manufacturer $manufacturer */
        $manufacturer = factory(Manufacturer::class)->create();
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->make());

        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.starcitizen.manufacturers.edit', $manufacturer->getRouteKey())
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('admin.starcitizen.manufacturers.edit')
                ->assertDontSee(__('Keine Hersteller vorhanden'))
                ->assertSee(__('de_DE'))
                ->assertSee(__('en_EN'))
                ->assertSee($manufacturer->name)
                ->assertSee($manufacturer->name_short);
        }
    }

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Manufacturer\ManufacturerController::edit
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     *
     * @covers \App\Exceptions\Handler
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.starcitizen.manufacturers.edit', static::MODEL_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }


    /**
     * Update Tests
     */

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Manufacturer\ManufacturerController::update
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     *
     * @covers \App\Http\Requests\ManufacturerTranslationRequest
     *
     * @covers \App\Models\Api\StarCitizen\Manufacturer\ManufacturerTranslation
     * @covers \App\Models\System\ModelChangelog
     */
    public function testUpdate()
    {
        /** @var Manufacturer $manufacturer */
        $manufacturer = factory(Manufacturer::class)->create();
        $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->make());

        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.starcitizen.manufacturers.update', $manufacturer->getRouteKey()),
            [
                'known_for_en_EN' => 'Hersteller Known For',
                'description_en_EN' => 'Hersteller description',
                'known_for_de_DE' => 'Hersteller Known For Deutsch',
                'description_de_DE' => 'Hersteller description Deutsch',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\Admin\StarCitizen\Manufacturer\ManufacturerController::update
     * @covers \App\Http\Controllers\Api\V1\StarCitizen\Manufacturer\ManufacturerController::show
     *
     * @covers \App\Exceptions\Handler
     */
    public function testUpdateNotFound()
    {
        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.starcitizen.manufacturers.update', static::MODEL_ID_NOT_EXISTENT),
            [
                'known_for_en_EN' => 'Hersteller Known For',
                'description_en_EN' => 'Hersteller description',
                'known_for_de_DE' => 'Hersteller Known For Deutsch',
                'description_de_DE' => 'Hersteller description Deutsch',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }

    /**
     * {@inheritdoc}
     * Creates needed Manufacturers
     */
    protected function setUp()
    {
        parent::setUp();
        factory(Manufacturer::class, 10)->create()->each(
            function (Manufacturer $manufacturer) {
                $manufacturer->translations()->save(factory(ManufacturerTranslation::class)->make());
            }
        );
    }
}
