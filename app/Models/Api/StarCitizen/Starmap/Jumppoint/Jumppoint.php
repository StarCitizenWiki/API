<?php
/**
 * User: Keonie
 * Date: 05.08.2018 17:25
 */

namespace App\Models\Api\StarCitizen\Starmap\Jumppoint;

use App\Models\Api\ModelChangelog;
use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Jumppoint
 * @package App\Models\Api\StarCitizen\Starmap\Jumppoint
 */
class Jumppoint extends Model
{
    protected $fillable = [
        'exclude',
        'cig_id',
        'size',
        'direction',
        'entry_cig_id',
        'entry_status',
        'entry_cig_system_id',
        'entry_code',
        'entry_designation',
        'exit_cig_id',
        'exit_status',
        'exit_cig_system_id',
        'exit_code',
        'exit_designation',
    ];

    //TODO szi Cast benötigt, wenn schon in richtigem Format?
    protected $casts = [

    ];

    protected $with = [
        'celestial_object',
    ];

    protected $table = 'jumppoint';

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entry()
    {
        return $this->belongsTo(CelestialObject::class, 'entry_cig_id', 'cig_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exit()
    {
        return $this->belongsTo(CelestialObject::class, 'exit_cig_id', 'cig_id');
    }

    /**
     * The saved changes
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function changelogs()
    {
        return $this->morphMany(ModelChangelog::class, 'changelog');
    }
}