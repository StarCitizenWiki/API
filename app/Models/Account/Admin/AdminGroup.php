<?php declare(strict_types = 1);

namespace App\Models\Account\Admin;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Groups
 */
class AdminGroup extends Model
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
     * Comm Link Editor
     */
    const EDITOR = 0;

    /**
     * Registrierter Account
     */
    const USER = 0;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function admins()
    {
        return $this->belongsToMany(Admin::class);
    }

    public function scopeAdmin(Builder $query)
    {
        $query->where('name', 'bureaucrat')->orWhere('name', 'sysop');
    }
}
