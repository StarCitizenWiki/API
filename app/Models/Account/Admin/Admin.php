<?php declare(strict_types = 1);

namespace App\Models\Account\Admin;

use App\Traits\ObfuscatesIDTrait as ObfuscatesID;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class Admin
 */
class Admin extends Authenticatable
{
    use Notifiable;
    use ObfuscatesID;

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];

    /**
     * @return bool
     */
    public function isBlocked(): bool
    {
        return (bool) $this->blocked;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany('App\Models\Admin\AdminGroup')->withTimestamps();
    }
}
