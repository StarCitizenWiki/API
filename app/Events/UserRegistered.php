<?php declare(strict_types = 1);

namespace App\Events;

use App\Models\Account\User\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Class UserRegistered
 */
class UserRegistered implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $user;
    public $password;

    /**
     * Create a new event instance.
     *
     * @param \App\Models\Account\User\User $user     The newly registered User
     * @param string                   $password The users randomly generated plaintext Password
     */
    public function __construct(User $user, string $password)
    {
        $this->password = $password;
        $this->user = $user;
    }
}
