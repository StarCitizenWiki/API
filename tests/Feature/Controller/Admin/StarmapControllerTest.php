<?php

namespace Tests\Feature\Controller\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class StarmapControllerTest
 * @package Tests\Feature\Controller\Admin
 */
class StarmapControllerTest extends TestCase
{
    use DatabaseTransactions;

    private $user;

    protected function setUp()
    {
        parent::setUp(); // TODO: Change the autogenerated stub
        $this->user = User::find(1);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\StarmapController::showStarmapSystemsView()
     */
    public function testStarmapSystemsView()
    {
        $response = $this->actingAs($this->user)->get('admin/starmap/systems');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\StarmapController::showAddStarmapSystemsView()
     */
    public function testAddStarmapSystemsView()
    {
        $response = $this->actingAs($this->user)->get('admin/starmap/systems/add');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\StarmapController::showEditStarmapSystemsView()
     */
    public function testEditStarmapSystemsView()
    {
        $response = $this->actingAs($this->user)->get('admin/starmap/systems/SOL');
        $response->assertStatus(200);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\StarmapController::deleteStarmapSystem()
     */
    public function testDeleteStarmapSystem()
    {
        $response = $this->actingAs($this->user)->delete('admin/starmap/systems', [
            'id' => 1,
        ]);
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\StarmapController::addStarmapSystem()
     */
    public function testAddStarmapSystem()
    {
        $response = $this->actingAs($this->user)->post('admin/starmap/systems', [
            'code' => 'NEWSYSTEM',
        ]);
        $response->assertStatus(302);
    }

    /**
     * @covers \App\Http\Controllers\Auth\Admin\StarmapController::updateStarmapSystem()
     */
    public function testUpdateStarmapSystem()
    {
        $response = $this->actingAs($this->user)->patch('admin/starmap/systems', [
            'id' => 1,
            'code' => 'NEWSYSTEM',
        ]);
        $response->assertStatus(302);
    }
}