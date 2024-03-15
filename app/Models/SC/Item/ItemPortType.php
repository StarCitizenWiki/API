<?php

namespace App\Models\SC\Item;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemPortType extends Model
{
    use HasFactory;

    protected $table = 'sc_item_port_type';

    protected $fillable = [
        'item_port_id',
        'item_type_id',
    ];

    public function subTypes(): HasMany
    {
        return $this->hasMany(ItemPortTypeSubType::class);
    }

    public function typeName(): BelongsTo
    {
        return $this->belongsTo(ItemType::class, 'item_type_id', 'id');
    }

    public function getTypeAttribute()
    {
        return $this->typeName->type;
    }
}
