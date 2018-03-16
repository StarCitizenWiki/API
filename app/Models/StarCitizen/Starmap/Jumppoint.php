<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 02.08.2017 17:33
 */

namespace App\Models\StarCitizen\Starmap;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Jumppoint
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
        'sourcedata',
    ];

    protected $table = 'jumppoints';

    /**
     * @return bool
     */
    public function isExcluded(): bool
    {
        return (bool) $this->exclude;
    }
}