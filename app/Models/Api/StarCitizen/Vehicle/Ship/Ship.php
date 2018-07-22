<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Ship;

use App\Models\Api\StarCitizen\Vehicle\AbstractVehicle as Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Ship Model
 */
class Ship extends Vehicle
{
    protected $fillable = [
        'cig_id',
        'name',
        'manufacturer_id',
        'production_status_id',
        'production_note_id',
        'vehicle_size_id',
        'vehicle_type_id',
        'length',
        'beam',
        'height',
        'mass',
        'cargo_capacity',
        'min_crew',
        'max_crew',
        'scm_speed',
        'afterburner_speed',
        'pitch_max',
        'yaw_max',
        'roll_max',
        'x_axis_acceleration',
        'y_axis_acceleration',
        'z_axis_acceleration',
        'chassis_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function description()
    {
        return $this->hasMany(ShipTranslation::class)->rightJoin(
            'languages',
            'ship_translations.language_id',
            '=',
            'languages.id'
        );
    }

    /**
     * Translations Joined with Languages
     *
     * @return \Illuminate\Support\Collection
     */
    public function descriptionsCollection(): Collection
    {
        $collection = DB::table('ship_translations')->select('*')->rightJoin(
            'languages',
            function ($join) {
                /** @var $join \Illuminate\Database\Query\JoinClause */
                $join->on(
                    'ship_translations.language_id',
                    '=',
                    'languages.id'
                )->where('ship_translations.ship_id', '=', $this->getKey());
            }
        )->get();

        return $collection->keyBy('locale_code');
    }
}
