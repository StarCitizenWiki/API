<?php declare(strict_types = 1);

namespace App\Models\Starmap;

use App\Traits\ObfuscatesIDTrait as ObfuscatesID;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Starsystem
 *
 * @package App\Models
 * @mixin \Eloquent
 * @property int            $id
 * @property string         $code
 * @property integer        $cig_id
 * @property string         $status
 * @property dateTime       $cig_time_modified
 * @property string         $type
 * @property string         $name
 * @property decimal        $position_x
 * @property decimal        $position_y
 * @property decimal        $position_z
 * @property string         $info_url
 * @property string         $description
 * @property integer        $affiliation_id
 * @property string         $affiliation_name
 * @property string         $affiliation_code
 * @property string         $affiliation_color
 * @property integer        $affiliation_membership_id
 * @property string         $aggregated_size
 * @property string         $aggregated_population
 * @property string         $aggregated_economy
 * @property string         $aggregated_danger
 * @property string         $sourcedata
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starsystem whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starsystem whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starsystem whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\Starsystem whereUpdatedAt($value)
 * @property int            $exclude
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\Starsystem whereExclude($value)
 */
class Starsystem extends Model
{
    use ObfuscatesID;

    protected $fillable = [
        'code',
        'cig_id',
        'status',
        'cig_time_modified',
        'type',
        'name',
        'position_x',
        'position_y',
        'position_z',
        'info_url',
        'description',
        'affiliation_id',
        'affiliation_name',
        'affiliation_code',
        'affiliation_color',
        'affiliation_membership_id',
        'aggregated_size',
        'aggregated_population',
        'aggregated_economy',
        'aggregated_danger',
        'sourcedata',
    ];

    protected $table = 'starsystems';

    /**
     * @param string $code
     *
     * @return string
     */
    public static function makeFilenameFromCode(string $code): String
    {
        return $code.'_System.json';
    }
}
