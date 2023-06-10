<?php

declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\StarCitizen\Vehicle\Focus;

use App\Http\Controllers\Web\User\StarCitizen\Vehicle\Focus\FocusController;
use App\Models\StarCitizen\Vehicle\Focus\Focus;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Controller\Web\User\StarCitizen\StarCitizenTestCase;

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
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Focus\FocusController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.starcitizen.vehicles.foci.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.vehicles.foci.index')
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
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Focus\FocusController::edit
     */
    public function testEdit()
    {
        /** @var \App\Models\StarCitizen\Vehicle\Focus\Focus $vehicleFocus */
        $vehicleFocus = Focus::factory()->create();

        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.vehicles.foci.edit', $vehicleFocus->getRouteKey())
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.vehicles.foci.edit')
                ->assertSee(__('Übersetzungen'))
                ->assertSee(__('Speichern'));
        }
    }

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Focus\FocusController::edit
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.vehicles.foci.edit', static::MODEL_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }


    /**
     * Update Tests
     */

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Focus\FocusController::update
     *
     * @covers \App\Http\Requests\System\TranslationRequest
     *
     * @covers \App\Models\System\ModelChangelog
     */
    public function testUpdate()
    {
        /** @var \App\Models\StarCitizen\Vehicle\Focus\Focus $vehicleFocus */
        $vehicleFocus = Focus::factory()->create();

        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.vehicles.foci.update', $vehicleFocus->getRouteKey()),
            [
                'en_EN' => 'Vehicle Focus translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        self::assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\Vehicle\Focus\FocusController::update
     */
    public function testUpdateNotFound()
    {
        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.vehicles.foci.update', static::MODEL_ID_NOT_EXISTENT),
            [
                'en_EN' => 'Vehicle Focus translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        self::assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }

    /**
     * {@inheritdoc}
     * Creates needed Vehicle Foci
     */
    protected function setUp(): void
    {
        parent::setUp();
        Focus::factory()->count(10)->create();
    }
}
