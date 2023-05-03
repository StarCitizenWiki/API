<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\CharArmor\CharArmor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ItemPort extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_item_ports';

    protected $fillable = [
        'name',
        'display_name',
        'position',
        'min_size',
        'max_size',
    ];

    protected $casts = [
        'min_size' => 'int',
        'max_size' => 'int',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class,
            'item_uuid',
            'uuid'
        );
    }

    public function loadout(): HasOne
    {
        return $this->hasOne(
            ItemPortLoadout::class,
            'item_port_id',
            'id'
        )->withDefault();
    }
}
