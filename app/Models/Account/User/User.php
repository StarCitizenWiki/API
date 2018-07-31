<?php declare(strict_types = 1);

namespace App\Models\Account\User;

use App\Events\ModelUpdating;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class User
 */
class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    use CanResetPassword;

    const STATE_DEFAULT = 0;
    const STATE_UNTHROTTLED = 1;
    const STATE_BLOCKED = 2;

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'api_token',
        'password',
        'requests_per_minute',
        'last_login',
        'notes',
        'api_token_last_used',
        'receive_notification_level',
        'state',
    ];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Checks if the whitelisted flag is set to true
     *
     * @return bool
     */
    public function isUnthrottled(): bool
    {
        return (int) $this->state === static::STATE_UNTHROTTLED;
    }

    /**
     * Checks if the blacklisted flag is set to true
     *
     * @return bool
     */
    public function isBlocked(): bool
    {
        return (int) $this->state === static::STATE_BLOCKED;
    }
}
