<?php

declare(strict_types=1);

namespace App\Models\StarCitizenUnpacked\Shop;

use App\Events\ModelUpdating;
use App\Models\StarCitizenUnpacked\Item;
use App\Traits\HasModelChangelogTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopItemRental extends Model
{
    use HasModelChangelogTrait;

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    public $timestamps = false;

    protected $table = 'star_citizen_unpacked_shop_item_rental';

    protected $fillable = [
        'item_id',
        'shop_id',
        'item_uuid',
        'shop_uuid',
        'percentage_1',
        'percentage_3',
        'percentage_7',
        'percentage_30',
        'version',
    ];

    protected $casts = [
        'percentage_1' => 'double',
        'percentage_3' => 'double',
        'percentage_7' => 'double',
        'percentage_30' => 'double',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'item_uuid', 'uuid');
    }

    public function shop(): BelongsTo
    {
        return $this->belongsTo(Shop::class, 'shop_uuid', 'uuid');
    }
}
