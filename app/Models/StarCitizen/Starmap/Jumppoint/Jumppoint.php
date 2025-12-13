<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Starmap\Jumppoint;

use App\Models\StarCitizen\Starmap\CelestialObject\CelestialObject;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Jumppoint
 */
class Jumppoint extends Model
{
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

    /**
     * Jump point entry object
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(CelestialObject::class, 'entry_id', 'cig_id');
    }

    /**
     * Jump point exit object
     */
    public function exit(): BelongsTo
    {
        return $this->belongsTo(CelestialObject::class, 'exit_id', 'cig_id');
    }
}
