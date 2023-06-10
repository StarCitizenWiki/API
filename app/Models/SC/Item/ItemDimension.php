<?php

declare(strict_types=1);

namespace App\Models\SC\Item;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemDimension extends Model
{
    use HasFactory;

    protected $table = 'sc_item_dimensions';

    protected $fillable = [
        'item_uuid',
        'width',
        'height',
        'length',
        'volume',
        'override',
    ];

    protected $casts = [
        'width' => 'double',
        'height' => 'double',
        'length' => 'double',
        'volume' => 'double',
        'override' => 'boolean',
    ];
}
