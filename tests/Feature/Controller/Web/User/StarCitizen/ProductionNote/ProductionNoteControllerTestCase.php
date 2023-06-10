<?php

declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\StarCitizen\ProductionNote;

use App\Http\Controllers\Web\User\StarCitizen\ProductionNote\ProductionNoteController;
use App\Models\StarCitizen\ProductionNote\ProductionNote;
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
        /** @var \App\Models\StarCitizen\ProductionNote\ProductionNote $productionNote */
        $productionNote = ProductionNote::factory()->create();

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
     * @covers \App\Http\Requests\System\TranslationRequest
     *
     * @covers \App\Models\System\ModelChangelog
     */
    public function testUpdate()
    {
        /** @var \App\Models\StarCitizen\ProductionNote\ProductionNote $productionNote */
        $productionNote = ProductionNote::factory()->create();

        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.production-notes.update', $productionNote->getRouteKey()),
            [
                'en_EN' => 'Vehicle ProductionNote translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        self::assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

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

        self::assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }

    /**
     * {@inheritdoc}
     * Creates needed Production Notes
     */
    protected function setUp(): void
    {
        parent::setUp();
        ProductionNote::factory()->count(10)->create();
    }
}
