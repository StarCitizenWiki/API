<?php declare(strict_types = 1);

namespace Tests\Feature\Model;

use App\Models\Account\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Class UserModelTest
 */
class UserModelTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @covers \App\Models\Account\User::updateUser()
     */
    public function testUpdate()
    {
        $user = User::find(1);
        $updated = $user->update([
            'id' => 1,
            'name' => 'Updated',
        ]);

        $this->assertTrue($updated);
    }

    /**
     * @covers \App\Models\Account\User::isUnthrottled()
     */
    public function testisUnthrottled()
    {
        $user = User::find(3);
        $this->assertTrue($user->isUnthrottled());
    }

    /**
     * @covers \App\Models\Account\User::isUnthrottled()
     */
    public function testIsNotWhitelisted()
    {
        $user = User::find(4);
        $this->assertFalse($user->isUnthrottled());
    }

    /**
     * @covers \App\Models\Account\User::isBlocked()
     */
    public function testisBlocked()
    {
        $user = User::find(4);
        $this->assertTrue($user->isBlocked());
    }

    /**
     * @covers \App\Models\Account\User::isBlocked()
     */
    public function testIsNotBlacklisted()
    {
        $user = User::find(2);
        $this->assertFalse($user->isBlocked());
    }
}
