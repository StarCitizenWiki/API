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

    protected $fillable = [
        'username',
        'blocked',
        'provider',
        'provider_id',
    ];

    protected $with = [
        'groups',
    ];

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

    public function getHighestPermissionLevel(): int
    {
        return $this->groups->first()->permission_level;
    }

    public function isEditor(): bool
    {
        $group = $this->groups()->where('name', 'editor')->first();

        return null === $group ? false : true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(AdminGroup::class)->orderByDesc('permission_level');
    }
}
