<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 05.08.2018 17:25
 */

namespace App\Models\Api\StarCitizen\Starmap\Jumppoint;

use App\Models\Api\StarCitizen\Starmap\CelestialObject\CelestialObject;
use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Jumppoint
 */
class Jumppoint extends Model
{
    use ModelChangelog;

    protected $fillable = [
        'exclude',
        'cig_id',
        'size',
        'direction',
        'entry_id',
        'entry_status',
        'entry_system_id',
        'entry_code',
        'entry_designation',
        'exit_id',
        'exit_status',
        'exit_system_id',
        'exit_code',
        'exit_designation',
    ];

    //TODO szi Cast benötigt, wenn schon in richtigem Format?
    protected $casts = [

    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function entry()
    {
        return $this->belongsTo(CelestialObject::class, 'entry_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function exit()
    {
        return $this->belongsTo(CelestialObject::class, 'exit_id', 'id');
    }
}
