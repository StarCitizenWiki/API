<?php

namespace App\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
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
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\ShortURL\ShortURL[] $shortURLs
 * @mixin \Eloquent
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $api_token
 * @property string $password
 * @property int $requests_per_minute
 * @property bool $whitelisted
 * @property bool $blacklisted
 * @property string $notes
 * @property string $last_login
 * @property string $api_token_last_used
 * @property string $remember_token
 * @property string $deleted_at
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereApiToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereApiTokenLastUsed($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereBlacklisted($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereEmail($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereLastLogin($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereName($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereNotes($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User wherePassword($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereRememberToken($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereRequestsPerMinute($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\User whereWhitelisted($value)
 */
class User extends Authenticatable
{
    use Notifiable, SoftDeletes, CanResetPassword;

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

        Log::info('User Account updated', [
            'changes' => $changes,
        ]);

        return $user->save();
    }

    /**
     * Checks if the current userid is in the defined AdminID Array
     *
     * @return bool
     */
    public function isAdmin() : bool
    {
        $isAdmin = in_array($this->id, AUTH_ADMIN_IDS);
        Log::debug('Checked if User is Admin', [
            'method' => __METHOD__,
            'id' => $this->id,
            'admin' => $isAdmin,
        ]);

        return $isAdmin;
    }

    /**
     * Checks if the whitelisted flag is set to true
     *
     * @return bool
     */
    public function isWhitelisted() : bool
    {
        $whitelisted = $this->whitelisted == 1;
        Log::debug('Checked if User is whitelisted', [
            'method' => __METHOD__,
            'id' => $this->id,
            'whitelisted' => $whitelisted,
        ]);

        return $whitelisted;
    }

    /**
     * Checks if the blacklisted flag is set to true
     *
     * @return bool
     */
    public function isBlacklisted() : bool
    {
        $blacklisted = $this->blacklisted == 1;
        Log::debug('Checked if User is blacklisted', [
            'method' => __METHOD__,
            'id' => $this->id,
            'blacklisted' => $blacklisted,
        ]);

        return $blacklisted;
    }

    /**
     * Sets the shorturl relation
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function shortURLs()
    {
        Log::debug('Requested Users ShortURLs', [
            'method' => __METHOD__,
            'id' => Auth::id(),
        ]);

        return $this->hasMany('App\Models\ShortURL\ShortURL');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function apiRequests()
    {
        return $this->hasMany('App\Models\APIRequests');
    }
}
