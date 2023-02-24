<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\WeaponPersonal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeaponPersonalAttachmentPort extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_personal_weapon_attachment_ports';

    protected $fillable = [
        'weapon_id',
        'name',
        'position',
        'min_size',
        'max_size',
    ];

    protected $casts = [
        'min_size' => 'int',
        'max_size' => 'int',
    ];

    /**
     * @return BelongsTo
     */
    public function weapon(): BelongsTo
    {
        return $this->belongsTo(WeaponPersonal::class, 'weapon_id');
    }
}
