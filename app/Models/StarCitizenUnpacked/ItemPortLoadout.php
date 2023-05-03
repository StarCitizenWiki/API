<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked;

use App\Models\StarCitizenUnpacked\CharArmor\CharArmor;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ItemPortLoadout extends Model
{
    use HasFactory;

    protected $table = 'star_citizen_unpacked_item_port_loadouts';

    protected $fillable = [
        'item_port_id',
        'parent_item_port_id',
        'equipped_item_uuid',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class,
            'equipped_item_uuid',
            'uuid'
        );
    }

    /**
     * Retrieve child hardpoints form the same table by joining on the parent_hardpoint_id attribute
     *
     * @return HasMany
     */
    public function children(): HasMany
    {
        return $this->hasMany(
            __CLASS__,
            'parent_item_port_id',
            'item_port_id',
        );
          //  ->where('item_port_id', $this->item_port_id);
    }

    /**
     * This is needed because fractal re-uses relations?
     * While this is exactly the same as above this truly retrieves the children from the DB
     *
     * @return HasMany
     */
    public function children2(): HasMany
    {
        return $this->hasMany(
            __CLASS__,
            'parent_item_port_id',
            'item_port_id',
        );
          //  ->where('item_port_id', $this->item_port_id);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(__CLASS__, 'parent_item_port_id', 'item_port_id')
            //->where('vehicle_id', $this->vehicle_id)
            ->withDefault();
    }
}
