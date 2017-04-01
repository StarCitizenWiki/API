<?php

namespace Tests\Feature\Model;

use App\Http\Controllers\Auth\RegisterController;
use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class UserModelTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @covers \App\Models\User::updateUser()
     */
    public function testUpdate()
    {
        $updated = User::updateUser([
            'id' => 1,
            'name' => 'Updated',
        ]);

        $this->assertTrue($updated);
    }

    /**
     * @covers \App\Models\User::isAdmin()
     */
    public function testIsAdmin()
    {
        $user = User::find(1);
        $this->assertTrue($user->isAdmin());
    }

    /**
     * @covers \App\Models\User::isAdmin()
     */
    public function testIsNotAdmin()
    {
        $user = User::find(2);
        $this->assertFalse($user->isAdmin());
    }

    /**
     * @covers \App\Models\User::isWhitelisted()
     */
    public function testIsWhitelisted()
    {
        $user = User::find(2);
        $this->assertTrue($user->isWhitelisted());
    }

    /**
     * @covers \App\Models\User::isWhitelisted()
     */
    public function testIsNotWhitelisted()
    {
        $user = User::find(3);
        $this->assertFalse($user->isWhitelisted());
    }

    /**
     * @covers \App\Models\User::isBlacklisted()
     */
    public function testIsBlacklisted()
    {
        $user = User::find(3);
        $this->assertTrue($user->isBlacklisted());
    }

    /**
     * @covers \App\Models\User::isBlacklisted()
     */
    public function testIsNotBlacklisted()
    {
        $user = User::find(2);
        $this->assertFalse($user->isBlacklisted());
    }
}
