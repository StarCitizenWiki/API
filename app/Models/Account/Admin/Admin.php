<?php declare(strict_types = 1);

namespace App\Models\Account\Admin;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class Admin
 */
class Admin extends Authenticatable
{
    use Notifiable;

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
        return $this->belongsToMany(AdminGroup::class)->withTimestamps();
    }
}
