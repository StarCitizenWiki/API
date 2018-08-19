<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Starmap;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Starsystem
 */
class Starsystem extends Model
{
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
