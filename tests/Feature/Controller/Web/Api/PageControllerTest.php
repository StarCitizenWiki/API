<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Web\Account;

use App\Models\Account\User\User;
use App\Models\Account\User\UserGroup;
use App\Models\Api\Notification;
use Tests\TestCase;

/**
 * Class Page Controller Test
 */
class PageControllerTest extends TestCase
{
    private $user;

    /**
     * @covers \App\Http\Controllers\Web\Api\PageController::index
     */
    public function testIndexView()
    {
        $response = $this->actingAs($this->user)->get(route('web.api.index'));
        $response->assertOk()
            ->assertViewIs('api.pages.index')
            ->assertSee($this->user->name);
    }

    /**
     * @covers \App\Http\Controllers\Web\Api\PageController::index
     */
    public function testIndexBlockedView()
    {
        $user = factory(User::class)->states('blocked')->create();

        $response = $this->actingAs($user)->get(route('web.api.index'));
        $response->assertStatus(403);
    }

    /**
     * @covers \App\Http\Controllers\Web\Api\PageController::showStatusView
     */
    public function testStatusView()
    {
        $response = $this->actingAs($this->user)->get(route('web.api.status'));
        $response->assertOk()
            ->assertViewIs('api.pages.status')
            ->assertSee(__('Keine Probleme gemeldet'));
    }

    /**
     * @covers \App\Http\Controllers\Web\Api\PageController::showStatusView
     */
    public function testStatusViewWithNotifications()
    {
        $notification = factory(Notification::class)->state('active')->create();
        $response = $this->actingAs($this->user)->get(route('web.api.status'));
        $response->assertOk()
            ->assertViewIs('api.pages.status')
            ->assertSee($notification->content);
    }

    /**
     * @covers \App\Http\Controllers\Web\Api\PageController::showFaqView
     */
    public function testFaqView()
    {
        $response = $this->actingAs($this->user)->get(route('web.api.faq'));
        $response->assertOk()
            ->assertViewIs('api.pages.faq');
    }

    /**
     * Creates a User in the DB
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->createUserGroups();
        $this->user = factory(User::class)->create();
        $this->user->groups()->sync(UserGroup::where('name', 'user')->first()->id);
    }
}
