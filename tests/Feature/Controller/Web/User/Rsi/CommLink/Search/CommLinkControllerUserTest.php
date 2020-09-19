<?php declare(strict_types=1);

namespace Tests\Feature\Controller\Web\User\Rsi\CommLink\Search;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Models\Rsi\CommLink\CommLink;
use Dingo\Api\Http\Response;

/**
 * Class Comm-Link Controller Test
 *
 * @covers \App\Policies\Web\User\Rsi\CommLink\CommLinkPolicy<extended>
 * @covers \App\Http\Requests\Rsi\CommLink\CommLinkSearchRequest
 *
 * @covers \App\Http\Middleware\CheckUserState
 *
 * @covers \App\Providers\RouteServiceProvider
 *
 * @covers \App\Models\Rsi\CommLink\CommLink
 */
class CommLinkControllerUserTest extends CommLinkSearchControllerTestCase
{
    protected const RESPONSE_STATUSES = [
        'search' => \Illuminate\Http\Response::HTTP_OK,
    ];
    protected $searchData;

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\CommLinkSearchController::searchByTitle
     */
    public function testSearch(): void
    {
        $this->commLinks->push(
            factory(CommLink::class)->create(
                [
                    'title' => 'Example Title 1',
                ]
            )
        );
        $this->commLinks->push(
            factory(CommLink::class)->create(
                [
                    'title' => 'Example Title 2',
                ]
            )
        );

        $response = $this->actingAs($this->user)->post(
            route('web.user.rsi.comm-links.search-by-title.post'),
            [
                'keyword' => 'Example',
            ]
        );

        $response->assertStatus(static::RESPONSE_STATUSES['search']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.rsi.comm_links.index');
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\User\Rsi\CommLink\CommLinkSearchController::search
     */
    public function testSearchView(): void
    {
        $response = $this->actingAs($this->user)->get(route('web.user.rsi.comm-links.search'));

        $response->assertStatus(static::RESPONSE_STATUSES['search']);

        if ($response->status() === Response::HTTP_OK) {
            $response->assertViewIs('user.rsi.comm_links.search');
        }
    }

    /**
     * {@inheritdoc}
     * Adds the specific group to the Admin model
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->user->groups()->sync(UserGroup::where('name', 'user')->first()->id);
    }
}
