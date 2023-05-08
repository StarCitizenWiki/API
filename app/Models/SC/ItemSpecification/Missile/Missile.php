<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification\Missile;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Missile extends Model
{
    use HasFactory;

    protected $table = 'sc_item_missiles';

    protected $with = [
        'damages',
    ];

    protected $fillable = [
        'item_uuid',
        'signal_type',
        'lock_time',
    ];

    protected $casts = [
        'lock_time' => 'double',
    ];

    public function damages(): HasMany
    {
        return $this->hasMany(MissileDamage::class, 'missile_id');
    }

    public function getDamageAttribute(): float
    {
        return $this->damages->reduce(function ($carry, $item) {
            return $carry + $item->damage;
        }, 0);
    }
}
