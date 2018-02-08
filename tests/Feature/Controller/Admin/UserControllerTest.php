<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin;

use App\Models\Admin\Admin;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class UserControllerTest
 * @package Tests\Feature\Controller\Admin
 */
class UserControllerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @var \App\Models\Admin\Admin
     */
    private $admin;

    /**
     * @var \App\Models\User
     */
    private $user;

    /**
     * @covers \App\Http\Controllers\Admin\\UserController::showEditUserView()
     * @covers \App\Http\Middleware\CheckIfAdmin
     */
    public function testEditUserView()
    {
        $response = $this->actingAs($this->admin, 'admin')->get("admin/user/{$this->user->getRouteKey()}");
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\UserController::showEditUserView()
     */
    public function testEditUserViewException()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('admin/user/NotExistent');
        $response->assertStatus(400);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\UserController::showUsersListView()
     */
    public function testUsersView()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('admin/user');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\UserController::deleteUser()
     */
    public function testDeleteUser()
    {
        $response = $this->actingAs($this->admin, 'admin')->delete("admin/user/{$this->user->getRouteKey()}");
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\UserController::deleteUser()
     */
    public function testDeleteUserException()
    {
        $response = $this->actingAs($this->admin, 'admin')->delete('admin/user/NotExistent');
        $response->assertStatus(400);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\UserController::restoreUser()
     */
    public function testRestoreUser()
    {
        $response = $this->actingAs($this->admin, 'admin')->post("admin/user/{$this->user->getRouteKey()}/restore");
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\UserController::updateUser()
     */
    public function testUpdateUser()
    {
        $response = $this->actingAs($this->admin, 'admin')->patch(
            "admin/user/{$this->user->getRouteKey()}",
            [
                'name'                => 'Star Citizen Wiki',
                'requests_per_minute' => 60,
                'api_token'           => str_random(60),
                'email'               => 'info@star-citizen.wiki',
                'whitelisted'         => true,
                'blacklisted'         => false,
                'notes'               => str_random(120),
                'password'            => null,
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
