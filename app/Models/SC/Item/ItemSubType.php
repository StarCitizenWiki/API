<?php

namespace App\Models\SC\Item;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemSubType extends Model
{
    use HasFactory;

    protected $table = 'sc_item_sub_types';

    protected $fillable = [
        'sub_type',
    ];
}
