<?php declare(strict_types = 1);

namespace App\Models\Account\Admin;

use App\Models\System\ModelChangelog;
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
        'email',
        'blocked',
        'provider',
        'provider_id',
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

    /**
     * @return bool
     */
    public function isBlocked(): bool
    {
        return (bool) $this->blocked;
    }

    /**
     * @return int Highest Permission Level
     */
    public function getHighestPermissionLevel(): int
    {
        return $this->groups->first()->permission_level;
    }

    /**
     * @return bool
     */
    public function isEditor(): bool
    {
        $group = $this->groups()->where('name', 'editor')->first();

        return null !== $group;
    }

    /**
     * Associated Changelogs
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function changelogs()
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
     * Returns only Admins with 'bureaucrat' or 'sysop' group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function adminGroup()
    {
        return $this->belongsToMany(AdminGroup::class)->admin();
    }

    /**
     * Returns only Admins with 'editor' group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function editorGroup()
    {
        return $this->belongsToMany(AdminGroup::class)->editor();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function groups()
    {
        return $this->belongsToMany(AdminGroup::class)->orderByDesc('permission_level');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function settings()
    {
        return $this->hasOne(AdminSetting::class);
    }
}
