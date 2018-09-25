<?php declare(strict_types = 1);
/**
 * Created by PhpStorm.
 * User: Hanne
 * Date: 12.08.2018
 * Time: 16:22
 */

namespace Tests\Feature\Controller\Web\User\StarCitizen\ProductionNote;

use App\Http\Controllers\Web\User\StarCitizen\ProductionNote\ProductionNoteController;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNote;
use App\Models\Api\StarCitizen\ProductionNote\ProductionNoteTranslation;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Controller\Web\User\StarCitizen\StarCitizenTestCase;

/**
 * Admin Production Note Controller Test Case
 */
class ProductionNoteControllerTestCase extends StarCitizenTestCase
{
    /**
     * Index Tests
     */

    /**
     * Test Index
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\ProductionNote\ProductionNoteController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.starcitizen.production-notes.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.production_notes.index')
                ->assertDontSee(__('Keine Übersetzungen vorhanden'))
                ->assertSee(__('Produktionsnotizen'))
                ->assertSee(__('en_EN'));
        }
    }


    /**
     * Edit Tests
     */

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\ProductionNote\ProductionNoteController::edit
     */
    public function testEdit()
    {
        /** @var \App\Models\Api\StarCitizen\ProductionNote\ProductionNote $productionNote */
        $productionNote = factory(ProductionNote::class)->create();
        $productionNote->translations()->save(factory(ProductionNoteTranslation::class)->make());

        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.production-notes.edit', $productionNote->getRouteKey())
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.production_notes.edit')
                ->assertSee(__('Übersetzungen'))
                ->assertSee(__('Speichern'));
        }
    }

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\ProductionNote\ProductionNoteController::edit
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.production-notes.edit', static::MODEL_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }


    /**
     * Update Tests
     */

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\ProductionNote\ProductionNoteController::update
     *
     * @covers \App\Http\Requests\TranslationRequest
     *
     * @covers \App\Models\System\ModelChangelog
     */
    public function testUpdate()
    {
        /** @var \App\Models\Api\StarCitizen\ProductionNote\ProductionNote $productionNote */
        $productionNote = factory(ProductionNote::class)->create();
        $productionNote->translations()->save(factory(ProductionNoteTranslation::class)->make());

        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.production-notes.update', $productionNote->getRouteKey()),
            [
                'en_EN' => 'Vehicle ProductionNote translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\ProductionNote\ProductionNoteController::update
     */
    public function testUpdateNotFound()
    {
        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.production-notes.update', static::MODEL_ID_NOT_EXISTENT),
            [
                'en_EN' => 'Production Notes translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        $this->assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\StarCitizen\ProductionNote\ProductionNoteController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(ProductionNoteController::class)->disableOriginalConstructor()->getMock();
        $controller->expects($this->once())->method('middleware')->with('auth');

        $reflectedClass = new \ReflectionClass(ProductionNoteController::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($controller);
    }

    /**
     * {@inheritdoc}
     * Creates needed Production Notes
     */
    protected function setUp()
    {
        parent::setUp();
        factory(ProductionNote::class, 10)->create()->each(
            function (ProductionNote $productionNote) {
                $productionNote->translations()->save(factory(ProductionNoteTranslation::class)->make());
            }
        );
    }
}