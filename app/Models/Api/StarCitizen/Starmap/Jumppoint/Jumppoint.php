<?php

declare(strict_types=1);

namespace App\Models\Api\StarCitizen\Starmap\Jumppoint;

use App\Events\ModelUpdating;
use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class Jumppoint
 */
class Jumppoint extends Model
{
    use ModelChangelog;

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

    //TODO szi Cast benötigt, wenn schon in richtigem Format?
    protected $casts = [

    ];

    protected $dispatchesEvents = [
        'updating' => ModelUpdating::class,
        'created' => ModelUpdating::class,
        'deleting' => ModelUpdating::class,
    ];

    /**
     * @return BelongsTo
     */
    public function entry(): BelongsTo
    {
        return $this->belongsTo(CelestialObject::class, 'entry_id', 'cig_id');
    }

    /**
     * @return BelongsTo
     */
    public function exit(): BelongsTo
    {
        return $this->belongsTo(CelestialObject::class, 'exit_id', 'cig_id');
    }
}
