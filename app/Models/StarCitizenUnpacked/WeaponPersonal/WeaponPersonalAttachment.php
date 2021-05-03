<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\WeaponPersonal;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeaponPersonalAttachment extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_personal_weapon_attachments';

    protected $fillable = [
        'weapon_id',
        'name',
        'position',
        'size',
        'grade',
    ];

    protected $casts = [
        'size' => 'int',
        'grade' => 'int',
    ];

    /**
     * @return BelongsTo
     */
    public function weapon(): BelongsTo
    {
        return $this->belongsTo(WeaponPersonal::class, 'weapon_id');
    }
}
