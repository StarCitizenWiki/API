<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

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
        'notes'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    public function isAdmin() : bool
    {
        if (in_array($this->id, AUTH_ADMIN_IDS)) {
            return true;
        }

        return false;
    }

    public function isWhitelisted() : bool
    {
        return $this->whitelisted == 1;
    }

    public function isBlacklisted() : bool
    {
        return $this->blacklisted == 1;
    }

}
