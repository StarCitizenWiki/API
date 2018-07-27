<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin;

use App\Models\Account\Admin\Admin;
use App\Models\Account\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class UserControllerTest
 */
class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var \App\Models\Account\Admin\Admin
     */
    private $admin;

    /**
     * @var \App\Models\Account\User
     */
    private $user;

    /**
     * @covers \App\Http\Controllers\Web\Admin\UserController::showEditUserView()
     * @covers \App\Http\Middleware\CheckIfAdmin
     */
    public function testEditUserView()
    {
        $response = $this->actingAs($this->admin, 'admin')->get("admin/users/{$this->user->getRouteKey()}/edit");
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\UserController::showEditUserView()
     */
    public function testShowUserViewException()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('admin/users/999999');
        $response->assertStatus(500);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\UserController::showEditUserView()
     */
    public function testEditUserViewNotFound()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('admin/users/99999/edit');
        $response->assertStatus(404);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\UserController::showUsersListView()
     */
    public function testUsersView()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('admin/users');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\UserController::deleteUser()
     */
    public function testDeleteUser()
    {
        $response = $this->actingAs($this->admin, 'admin')->delete("admin/users/{$this->user->getRouteKey()}");
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\UserController::deleteUser()
     */
    public function testDeleteUserNotFound()
    {
        $response = $this->actingAs($this->admin, 'admin')->delete('admin/users/NotExistent');
        $response->assertStatus(404);
    }

    /**
     * @covers \App\Http\Controllers\Web\Admin\UserController::updateUser()
     */
    public function testUpdateUser()
    {
        $response = $this->actingAs($this->admin, 'admin')->patch(
            "admin/users/{$this->user->getRouteKey()}",
            [
                'name' => 'Star Citizen Wiki',
                'requests_per_minute' => 60,
                'api_token' => str_random(60),
                'email' => 'info@star-citizen.wiki',
                'whitelisted' => true,
                'blacklisted' => false,
                'notes' => str_random(120),
                'password' => null,
            ]
        );
        $response->assertStatus(302);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->admin = Admin::find(1);
        $this->user = User::find(1);
    }
}
