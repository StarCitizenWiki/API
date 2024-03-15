<?php

namespace App\Models\SC\Item;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPortTypeSubType extends Model
{
    use HasFactory;

    protected $table = 'sc_item_port_type_sub_type';

    protected $fillable = [
        'item_port_type_id',
        'sub_type_id',
    ];

    public function subTypeName(): BelongsTo
    {
        return $this->belongsTo(ItemSubType::class, 'sub_type_id', 'id');
    }

    public function getSubTypeAttribute()
    {
        return $this->subTypeName->sub_type;
    }
}
