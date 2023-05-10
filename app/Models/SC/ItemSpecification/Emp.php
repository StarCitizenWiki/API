<?php

declare(strict_types=1);

namespace App\Models\SC\ItemSpecification;

use App\Models\SC\Item\Item;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Emp extends Model
{
    use HasFactory;

    protected $table = 'sc_item_emps';

    protected $fillable = [
        'item_uuid',
        'charge_duration',
        'emp_radius',
        'unleash_duration',
        'cooldown_duration',
    ];

    protected $casts = [
        'charge_duration' => 'double',
        'emp_radius' => 'double',
        'unleash_duration' => 'double',
        'cooldown_duration' => 'double',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(
            Item::class,
            'item_uuid',
            'uuid'
        );
    }
}
