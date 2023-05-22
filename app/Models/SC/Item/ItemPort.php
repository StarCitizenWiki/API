<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ItemPort extends Model
{
    use HasFactory;

    protected $with = [
        'item'
    ];

    protected $table = 'sc_item_ports';

    protected $fillable = [
        'name',
        'display_name',
        'equipped_item_uuid',
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
            'equipped_item_uuid',
            'uuid'
        );
    }

    public function getPositionAttribute(): ?string
    {
        if (empty($this->name)) {
            return null;
        }

        $default = strtoupper(explode($this->name, '_attach')[0] ?? '');
        $default = empty($default) ? null : $default;

        return match ($this->name) {
            'barrel_attach' => 'Barrel',
            'canister_attach' => 'Canister',
            'magazine_attach' => 'Magazine',
            'missile_attach_01' => 'Missile',
            'module_attach' => 'Module',
            'optics_attach' => 'Optics',
            'underbarrel_attach' => 'Underbarrel',
            'weapon_action_attachment' => 'Weapon Action',
            default => $default,
        };
    }
}
