<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\WeaponPersonal;

use App\Models\StarCitizenUnpacked\CommodityItem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class OpticAttachment extends CommodityItem
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_personal_weapon_optic_attachments';

    protected $fillable = [
        'attachment_id',
        'magnification',
        'type',
    ];

    public function attachment(): BelongsTo
    {
        return $this->belongsTo(Attachment::class, 'attachment_id');
    }
}
