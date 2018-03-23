<?php declare(strict_types = 1);

namespace App\Models;

use App\Traits\ObfuscatesIDTrait as ObfuscatesID;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

/**
 * Class User
 */
class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    use CanResetPassword;
    use ObfuscatesID;

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

    const STATE_DEFAULT = 0;
    const STATE_UNTHROTTLED = 1;
    const STATE_BLOCKED = 2;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Updates a User Model
     *
     * @param array $data UserData
     *
     * @return bool
     */
    public static function updateUser(array $data): bool
    {
        $changes = [];
        $changes[] = [
            'updated_by' => Auth::id(),
        ];

        $user = User::withTrashed()->findOrFail($data['id']);

        foreach ($data as $key => $value) {
            if ($user->$key != $value) {
                if ('password' !== $key) {
                    $changes[] = [
                        $key.'_old' => $user->$key,
                        $key.'_new' => $value,
                    ];
                    $user->$key = $value;
                } else {
                    $user->password = bcrypt($data['password']);
                    $changes[] = [
                        'password_changed' => true,
                    ];
                }
            }
        }

        app('Log')::notice('User Account updated', ['changes' => $changes]);

        return $user->save();
    }

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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function apiRequests()
    {
        return $this->hasMany('App\Models\ApiRequests');
    }
}
