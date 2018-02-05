<?php declare(strict_types = 1);
/**
 * User: Keonie
 * Date: 02.08.2017 17:33
 */

namespace App\Models\Starmap;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Jumppoint
 * @package App\Models
 * @mixin \Eloquent
 * @property int        $id
 * @property boolean    $exclude
 * @property int        $cig_id
 * @property string     $size
 * @property string     $direction
 * @property int        $entry_cig_id
 * @property string     $entry_status
 * @property int        $entry_cig_system_id
 * @property string     $entry_code
 * @property string     $entry_designation
 * @property int        $exit_cig_id
 * @property string     $exit_status
 * @property int        $exit_cig_system_id
 * @property string     $exit_code
 * @property string     $exit_designation
 * @property string     $sourcedata
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starmap\Jumppoint whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starmap\Jumppoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starmap\Jumppoint whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starmap\Jumppoint whereUpdatedAt($value)
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