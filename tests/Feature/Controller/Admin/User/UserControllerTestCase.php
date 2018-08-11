<?php declare(strict_types = 1);
/**
 * User: Hannes
 * Date: 08.08.2018
 * Time: 13:28
 */

namespace Tests\Feature\Controller\Admin\User;

use App\Models\Account\User\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Class AbstractBaseUserControllerTestCase
 *
 * @covers \App\Policies\Web\Admin\User\UserPolicy<extended>
 *
 * @covers \App\Models\Account\User\User
 *
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfNotAdmin
 * @covers \App\Http\Middleware\Web\Admin\RedirectIfAdmin
 * @covers \App\Http\Middleware\CheckUserState
 */
class UserControllerTestCase extends TestCase
{
    use RefreshDatabase;

    protected const RESPONSE_STATUSES = [];

    protected const USER_ID_NOT_EXISTENT = 999999;

    /**
     * @var \App\Models\Account\Admin\Admin
     */
    protected $admin;

    /**
     * @covers \App\Http\Controllers\Web\Admin\User\UserController::index
     */
    public function testIndex()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.users.index'));
        $response->assertStatus(static::RESPONSE_STATUSES['index']);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\User\UserController::edit
     */
    public function testEdit()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($this->admin, 'admin')->get(route('web.admin.users.edit', $user->getRouteKey()));
        $response->assertStatus(static::RESPONSE_STATUSES['edit']);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\User\UserController::edit
     */
    public function testEditNotFound()
    {
        $response = $this->actingAs($this->admin, 'admin')->get(
            route('web.admin.users.edit', self::USER_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['edit_not_found']);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\User\UserController::update
     */
    public function testUpdate()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.users.update', $user->getRouteKey()),
            [
                'name' => 'Star Citizen Wiki',
                'requests_per_minute' => 60,
                'api_token' => str_random(60),
                'email' => 'info@star-citizen.wiki',
                'state' => User::STATE_DEFAULT,
                'notes' => str_random(120),
                'password' => null,
            ]
        );
        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\User\UserController::update
     */
    public function testUpdateNotFound()
    {
        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.users.update', self::USER_ID_NOT_EXISTENT),
            [
                'name' => 'Star Citizen Wiki',
                'requests_per_minute' => 60,
                'api_token' => str_random(60),
                'email' => 'info@star-citizen.wiki',
                'state' => User::STATE_UNTHROTTLED,
                'notes' => str_random(120),
                'password' => null,
            ]
        );
        $response->assertStatus(static::RESPONSE_STATUSES['update_not_found']);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\User\UserController::update
     */
    public function testUpdatePassword()
    {
        $user = factory(User::class)->create();
        $password = $user->password;

        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.users.update', $user->getRouteKey()),
            [
                'name' => 'Star Citizen Wiki',
                'requests_per_minute' => 60,
                'api_token' => str_random(60),
                'email' => 'info@star-citizen.wiki',
                'state' => User::STATE_DEFAULT,
                'notes' => str_random(120),
                'password' => 'test',
                'password_confirmation' => 'test',
            ]
        );

        $user = User::find($user->id);

        $response->assertStatus(static::RESPONSE_STATUSES['update']);
        if ($response->status() !== 403) {
            $this->assertNotEquals($user->password, $password);
        }
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\User\UserController::update
     * @covers \App\Http\Controllers\Web\Admin\User\UserController::restore
     */
    public function testRestore()
    {
        $user = factory(User::class)->state('deleted')->create();

        $response = $this->actingAs($this->admin, 'admin')->patch(
            route('web.admin.users.update', $user->getRouteKey()),
            [
                'restore' => true,
            ]
        );
        $response->assertStatus(static::RESPONSE_STATUSES['update']);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\User\UserController::destroy
     */
    public function testDelete()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($this->admin, 'admin')->delete(
            route('web.admin.users.destroy', $user->getRouteKey())
        );
        $response->assertStatus(static::RESPONSE_STATUSES['delete']);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\User\UserController::destroy
     */
    public function testDeleteNotFound()
    {
        $response = $this->actingAs($this->admin, 'admin')->delete(
            route('web.admin.users.destroy', self::USER_ID_NOT_EXISTENT)
        );
        $response->assertStatus(static::RESPONSE_STATUSES['delete_not_found']);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
        $this->createAdminGroups();
    }
}
