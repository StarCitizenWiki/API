<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class MagazineAttachment extends CommodityItem
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_personal_weapon_magazine_attachments';

    protected $fillable = [
        'attachment_id',
        'capacity',
    ];

    protected $casts = [
        'capacity' => 'int',
    ];

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }
}
