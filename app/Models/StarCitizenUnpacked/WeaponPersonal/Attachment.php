<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Attachment extends CommodityItem
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_personal_weapon_attachments';

    protected $fillable = [
        'attachment_name',
        'uuid',
        'position',
        'size',
        'grade',
        'type',
        'version',
    ];

    protected $casts = [
        'size' => 'int',
        'grade' => 'int',
    ];

    public function weapons(): BelongsToMany
    {
        return $this->belongsToMany(
            WeaponPersonal::class,
            'star_citizen_unpacked_personal_weapons',
            'attachment_id',
            'weapon_id'
        );
    }

    public function optics(): HasOne
    {
        return $this->hasOne(OpticAttachment::class, 'attachment_id');
    }

    public function magazine(): HasOne
    {
        return $this->hasOne(MagazineAttachment::class, 'attachment_id');
    }
}
