<?php

namespace App\Models\StarCitizenUnpacked;

use App\Contracts\HasChangelogsInterface;
use App\Events\ModelUpdating;
use App\Models\System\Translation\AbstractHasTranslations as HasTranslations;
use App\Traits\HasModelChangelogTrait;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WeaponPersonal extends HasTranslations implements HasChangelogsInterface
{
    use HasModelChangelogTrait;

    protected $table = 'star_citizen_unpacked_personal_weapons';

    protected $fillable = [
        'name',
        'size',
        'manufacturer',
        'type',
        'class',
        'magazine_size',
        'effective_range',
        'rof',
        'attachment_size_optics',
        'attachment_size_barrel',
        'attachment_size_underbarrel',
        'ammunition_speed',
        'ammunition_range',
        'ammunition_damage',
    ];

    protected $casts = [
        'size' => 'int',
        'magazine_size' => 'int',
        'effective_range',
        'ammunition_speed' => 'double',
        'ammunition_range' => 'double',
        'ammunition_damage' => 'double',
    ];

    protected $with = [
        'modes'
    ];

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    /**
     * @return HasMany
     */
    public function translations()
    {
        return $this->hasMany(WeaponPersonalTranslation::class, 'weapon_id', 'id');
    }

    /**
     * @return HasMany
     */
    public function modes(): HasMany
    {
        return $this->hasMany(WeaponPersonalMode::class, 'weapon_id', 'id');
    }
}
