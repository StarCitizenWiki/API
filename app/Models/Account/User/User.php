<?php

declare(strict_types=1);

namespace App\Models\Account\User;

use App\Models\System\ModelChangelog;
use App\Models\System\Session;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Class Admin
 */
class User extends Authenticatable
{
    use Notifiable;
    use HasFactory;

    protected $fillable = [
        'username',
        'email',
        'blocked',
        'provider',
        'provider_id',
        'api_token',
    ];

    protected $with = [
        'groups',
        'settings',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'remember_token',
    ];

    protected $casts = [
        'blocked' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'last_login' => 'datetime',
    ];

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->getHighestPermissionLevel() >= UserGroup::SYSOP;
    }

    /**
     * @return int Highest Permission Level
     */
    public function getHighestPermissionLevel(): int
    {
        return $this->groups->first()->permission_level;
    }

    /**
     * Associated Changelogs
     *
     * @return HasMany
     */
    public function changelogs(): HasMany
    {
        return $this->hasMany(ModelChangelog::class);
    }

    /**
     * Function that generates a Link to the Wiki User
     *
     * @return string
     */
    public function userNameWikiLink()
    {
        return sprintf('%s/Benutzer:%s', config('api.wiki_url'), $this->username);
    }

    /**
     * Returns only Users with 'bureaucrat' or 'sysop' group
     *
     * @return BelongsToMany
     */
    public function adminGroup(): BelongsToMany
    {
        return $this->groups()->admin();
    }

    /**
     * @return BelongsToMany
     */
    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class)->orderByDesc('permission_level');
    }

    /**
     * @return HasOne
     */
    public function settings(): HasOne
    {
        return $this->hasOne(UserSetting::class)->withDefault();
    }

    /**
     * @return bool
     */
    public function receiveApiNotifications(): bool
    {
        return $this->settings->receive_api_notifications ?? false;
    }

    /**
     * @return bool
     */
    public function receiveCommLinkNotifications(): bool
    {
        return $this->settings->receive_comm_link_notifications ?? false;
    }

    /**
     * @return HasMany
     */
    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'user_id', 'id');
    }
}
