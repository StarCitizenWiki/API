<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\WeaponPersonal;

use App\Events\ModelUpdating;
use App\Traits\HasModelChangelogTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeaponPersonalMode extends Model
{
    use HasFactory;
    use HasModelChangelogTrait;

    protected $table = 'star_citizen_unpacked_personal_weapon_modes';

    protected $fillable = [
        'weapon_id',
        'mode',
        'rpm',
        'dps',
    ];

    protected $casts = [
        'rpm' => 'double',
        'dps' => 'double',
    ];

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    /**
     * @return BelongsTo
     */
    public function weapon(): BelongsTo
    {
        return $this->belongsTo(WeaponPersonal::class, 'weapon_id');
    }
}
