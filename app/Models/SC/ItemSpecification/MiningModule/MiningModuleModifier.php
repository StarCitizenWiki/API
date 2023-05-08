<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification\MiningModule;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiningModuleModifier extends Model
{
    use HasFactory;

    protected $table = 'sc_item_mining_module_modifiers';

    protected $fillable = [
        'mining_module_id',
        'name',
        'modifier',
    ];
}
