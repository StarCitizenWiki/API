<?php declare(strict_types = 1);

namespace App\Models\Starmap;

use App\Traits\ObfuscatesIDTrait as ObfuscatesID;
use Illuminate\Database\Eloquent\Model;

/**
 * Class CelestialObject
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
