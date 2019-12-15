<?php declare(strict_types = 1);

namespace App\Models\Account\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Groups
 */
class UserGroup extends Model
{
    /**
     * Oberste Berechtigungsstufe = Ernennung von Admins
     */
    const BUREAUCRAT = 4;

    /**
     * Admin
     */
    const SYSOP = 3;

    /**
     * User
     */
    const SICHTER = 2;

    /**
     * Mitarbeiter
     */
    const MITARBEITER = 1;

    /**
     * Registrierter Account
     */
    const USER = 0;

    protected $casts = [
        'permission_level' => 'int'
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function admins()
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Scope that Targets only Admins
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public function scopeAdmin(Builder $query)
    {
        $query->where('name', 'bureaucrat')->orWhere('name', 'sysop');
    }
}
