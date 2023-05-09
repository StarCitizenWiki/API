<?php

declare(strict_types=1);

namespace App\Models\SC\Shop;

use App\Events\ModelUpdating;
use App\Models\SC\Item\Item;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShopItemRental extends Model
{
    use ModelChangelog;

    protected $primaryKey = 'node_uuid';

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    public $timestamps = false;
    public $incrementing = false;

    protected $table = 'sc_shop_item_rentals';

    protected $fillable = [
        'item_id',
        'shop_id',
        'item_uuid',
        'shop_uuid',
        'node_uuid',
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
