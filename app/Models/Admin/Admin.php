<?php declare(strict_types = 1);

namespace App\Models\Admin;

use App\Traits\ObfuscatesIDTrait as ObfuscatesID;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class Admin
 * @package App\Models
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
        return $this->blocked == true;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany('App\Models\Admin\Group', 'admin_groups')->withTimestamps();
    }
}
