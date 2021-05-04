<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\CharArmor;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CharArmorResistance extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_char_armor_resistances';

    protected $fillable = [
        'type',
        'multiplier',
        'threshold',
    ];

    protected $casts = [
        'multiplier' => 'double',
        'threshold' => 'double',
    ];

    public $timestamps = false;

    public function armor(): BelongsToMany
    {
        return $this->belongsToMany(
            CharArmor::class,
            'star_citizen_unpacked_char_armor_attachment'
        );
    }
}
