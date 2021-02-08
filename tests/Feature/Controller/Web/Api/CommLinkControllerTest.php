<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\Api;

use App\Models\Rsi\CommLink\CommLink;
use Tests\TestCase;

/**
 * Public Comm Link preview tests
 */
class CommLinkControllerTest extends TestCase
{
    /**
     * @covers \App\Http\Controllers\Web\Api\CommLinkController::show
     */
    public function testShowView(): void
    {
        $commLink = CommLink::query()->first();

        $response = $this->get(route('web.api.comm-links.show', $commLink->getRouteKey()));
        $response->assertOk()
            ->assertViewIs('api.pages.comm_links.show')
            ->assertSee($commLink->title);
    }

    /**
     * Creates a User in the DB
     */
    protected function setUp(): void
    {
        parent::setUp();
        CommLink::factory()->create();
    }
}
