<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification\Missile;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MissileDamage extends Model
{
    use HasFactory;

    protected $table = 'sc_item_missile_damages';

    protected $fillable = [
        'missile_id',
        'type',
        'name',
        'damage',
    ];

    protected $casts = [
        'damage' => 'double',
    ];

    public function weapon(): BelongsTo
    {
        return $this->belongsTo(Missile::class, 'missile_id', 'id');
    }
}
