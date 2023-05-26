<?php

declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\StarCitizen\ProductionStatus;

use App\Http\Controllers\Web\User\StarCitizen\ProductionStatus\ProductionStatusController;
use App\Models\StarCitizen\ProductionStatus\ProductionStatus;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Tests\Feature\Controller\Web\User\StarCitizen\StarCitizenTestCase;

/**
 * Admin Production Status Controller Test Case
 */
class ProductionStatusControllerTestCase extends StarCitizenTestCase
{
    /**
     * Index Tests
     */

    /**
     * Test Index
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\ProductionStatus\ProductionStatusController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->user)->get(route('web.user.starcitizen.production-statuses.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.production_statuses.index')
#                ->assertDontSee(__('Keine Übersetzungen vorhanden'))
                ->assertSee(__('Produktionsstatus'))
                ->assertSee(__('en_EN'));
        }
    }


    /**
     * Edit Tests
     */

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\ProductionStatus\ProductionStatusController::edit
     */
    public function testEdit()
    {
        /** @var \App\Models\StarCitizen\ProductionStatus\ProductionStatus $productionStatus */
        $productionStatus = ProductionStatus::factory()->create();

        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.production-statuses.edit', $productionStatus->getRouteKey())
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.starcitizen.production_statuses.edit')
                ->assertSee(__('Übersetzungen'))
                ->assertSee(__('Speichern'));
        }
    }

    /**
     * Test Edit
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\ProductionStatus\ProductionStatusController::edit
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->user)->get(
            route('web.user.starcitizen.production-statuses.edit', static::MODEL_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }


    /**
     * Update Tests
     */

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\ProductionStatus\ProductionStatusController::update
     *
     * @covers \App\Http\Requests\System\TranslationRequest
     *
     * @covers \App\Models\System\ModelChangelog
     */
    public function testUpdate()
    {
        /** @var \App\Models\StarCitizen\ProductionStatus\ProductionStatus $productionStatus */
        $productionStatus = ProductionStatus::factory()->create();

        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.production-statuses.update', $productionStatus->getRouteKey()),
            [
                'en_EN' => 'Vehicle ProductionStatus translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        self::assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * Test Update
     *
     * @covers \App\Http\Controllers\Web\User\StarCitizen\ProductionStatus\ProductionStatusController::update
     */
    public function testUpdateNotFound()
    {
        $response = $this->actingAs($this->user)->patch(
            route('web.user.starcitizen.production-statuses.update', static::MODEL_ID_NOT_EXISTENT),
            [
                'en_EN' => 'Production Statuss translation',
                'de_DE' => 'Translation Deutsch',
            ]
        );

        self::assertNotEquals(ValidationException::class, get_class($response->exception ?? new \stdClass()));

        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }

    /**
     * @covers \App\Http\Controllers\Web\User\StarCitizen\ProductionStatus\ProductionStatusController
     */
    public function testConstructor()
    {
        $controller = $this->getMockBuilder(ProductionStatusController::class)->disableOriginalConstructor()->getMock();
        $controller->expects(self::once())->method('middleware')->with('auth');
    }

    /**
     * {@inheritdoc}
     * Creates needed Production Statuss
     */
    protected function setUp(): void
    {
        parent::setUp();
        ProductionStatus::factory()->count(10);
    }
}
