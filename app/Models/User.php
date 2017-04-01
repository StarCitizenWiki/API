<?php

namespace App\Models;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Class User
 *
 * @package App\Models
 */
class User extends Authenticatable
{
    use Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email',
        'api_token',
        'password',
        'requests_per_minute',
        'last_login',
        'notes',
        'api_token_last_used',
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
     * Updates a User Model
     *
     * @param array $data UserData
     *
     * @return bool
     */
    public static function updateUser(array $data) : bool
    {
        $changes = [];
        $changes[] = [
            'updated_by' => Auth::id(),
        ];

        $user = User::withTrashed()->findOrFail($data['id']);

        foreach ($data as $key => $value) {
            if ($user->$key != $value) {
                if ($key !== 'password') {
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

        Log::info('User Account updated', $changes);

        return $user->save();
    }

    /**
     * Checks if the current userid is in the defined AdminID Array
     *
     * @return bool
     */
    public function isAdmin() : bool
    {
        return in_array($this->id, AUTH_ADMIN_IDS);
    }

    /**
     * Checks if the whitelisted flag is set to true
     *
     * @return bool
     */
    public function isWhitelisted() : bool
    {
        return $this->whitelisted == 1;
    }

    /**
     * Checks if the blacklisted flag is set to true
     *
     * @return bool
     */
    public function isBlacklisted() : bool
    {
        return $this->blacklisted == 1;
    }

    /**
     * Sets the shorturl relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shortURLs()
    {
        return $this->hasMany('App\Models\ShortURL\ShortURL');
    }
}
