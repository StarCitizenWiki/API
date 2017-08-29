<?php declare(strict_types = 1);

namespace App\Models;

use App\Traits\ObfuscatesIDTrait as ObfuscatesID;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CelestialObject
 *
 * @package App\Models
 * @mixin \Eloquent
 * @property int      $id
 * @property string   $code
 * @property integer  $cig_id
 * @property integer  $cig_system_id
 * @property dateTime $cig_time_modified
 * @property string   $type
 * @property string   $designation
 * @property string   $name
 * @property decimal  $age
 * @property decimal  $distance
 * @property decimal  $latitude
 * @property decimal  $longitude
 * @property decimal  $axial_tilt
 * @property decimal  $orbit_period
 * @property string   $description
 * @property string   $info_url
 * @property bool     $habitable
 * @property bool     $fairchanceact
 * @property bool     $show_orbitlines
 * @property bool     $show_label
 * @property string   $appearance
 * @property integer  $sensor_population
 * @property integer  $sensor_economy
 * @property integer  $sensor_danger
 * @property string   $shader_data
 * @property decimal  $size
 * @property integer  $parent_id
 * @property integer  $subtype_id
 * @property string   $subtype_name
 * @property string   $subtype_type
 * @property integer  $affiliation_id
 * @property string   $affiliation_name
 * @property string   $affiliation_code
 * @property string   $affiliation_color
 * @property integer  $affiliation_membership_id
 * @property string   $population
 * @property string   $sourcedata
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CelestialObject whereCode($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CelestialObject whereCreatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CelestialObject whereId($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Models\CelestialObject whereUpdatedAt($value)
 */
class CelestialObject extends Model
{
    use ObfuscatesID;

    protected $fillable = [
        'code',
        'exclude',
        'cig_id',
        'cig_system_id',
        'cig_time_modified',
        'type',
        'designation',
        'name',
        'age',
        'distance',
        'latitude',
        'longitude',
        'axial_tilt',
        'orbit_period',
        'description',
        'info_url',
        'habitable',
        'fairchanceact',
        'show_orbitlines',
        'show_label',
        'appearance',
        'sensor_population',
        'sensor_economy',
        'sensor_danger',
        'shader_data',
        'size',
        'parent_id',
        'subtype_id',
        'subtype_name',
        'subtype_type',
        'affiliation_id',
        'affiliation_name',
        'affiliation_code',
        'affiliation_color',
        'affiliation_membership_id',
        'population',
        'sourcedata',
    ];

    protected $table = 'celestial_objects';

    /**
     * @return bool
     */
    public function isExcluded(): bool
    {
        return (bool) $this->exclude;
    }
}
