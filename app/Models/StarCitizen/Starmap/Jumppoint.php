<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Starmap;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jumppoint extends Model
{
    use HasFactory;

    protected $table = 'starmap_jumppoints';

    protected $fillable = [
        'cig_id',
        'direction',
        'entry_id',
        'exit_id',
        'name',
        'size',
        'entry_status',
        'exit_status',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(CelestialObject::class, 'entry_id', 'cig_id');
    }

    public function exit(): BelongsTo
    {
        return $this->belongsTo(CelestialObject::class, 'exit_id', 'cig_id');
    }
}
