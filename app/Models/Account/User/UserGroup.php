<?php

declare(strict_types=1);

namespace App\Models\Account\User;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Class Groups
 */
class UserGroup extends Model
{
    use HasFactory;

    /**
     * Oberste Berechtigungsstufe = Ernennung von Admins
     */
    public const BUREAUCRAT = 4;

    /**
     * Admin
     */
    public const SYSOP = 3;

    /**
     * User
     */
    public const SICHTER = 2;

    /**
     * Mitarbeiter
     */
    public const MITARBEITER = 1;

    /**
     * Registrierter Account
     */
    public const USER = 0;

    protected $casts = [
        'permission_level' => 'int',
    ];

    protected $fillable = [
        'name',
        'permission_level',
    ];

    /**
     * @return BelongsToMany
     */
    public function admins(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Scope that Targets only Admins
     *
     * @param Builder $query
     */
    public function scopeAdmin(Builder $query): void
    {
        $query->where('name', 'bureaucrat')->orWhere('name', 'sysop');
    }
}
