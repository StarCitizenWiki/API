<?php declare(strict_types = 1);

namespace Tests\Feature\Controller\Admin;

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class StarmapControllerTest
 * @package Tests\Feature\Controller\Admin
 */
class StarmapControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $admin;

    /**
     * @covers \App\Http\Controllers\Admin\\StarmapController::showStarmapSystemsView()
     */
    public function testStarmapSystemsView()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('admin/starmap/systems');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Admin\\StarmapController::showStarmapCelestialObjectView()
     */
    public function testStarmapCelestialObjectsView()
    {
        $response = $this->actingAs($this->admin, 'admin')->get('admin/starmap/celestialobject');
        $response->assertStatus(200);
    }

    protected function setUp()
    {
        parent::setUp();
        $this->admin = Admin::find(1);
    }
}
