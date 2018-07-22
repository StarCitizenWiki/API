<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\GroundVehicle;

use App\Models\Api\StarCitizen\Vehicle\AbstractVehicle as Vehicle;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Ground Vehicle Class
 */
class GroundVehicle extends Vehicle
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
        'chassis_id',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function description()
    {
        return $this->hasMany(GroundVehicleTranslation::class)->join(
            'languages',
            'ground_vehicle_translations.language_id',
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
        $collection = DB::table('ground_vehicle_translations')->select('*')->rightJoin(
            'languages',
            function ($join) {
                /** @var $join \Illuminate\Database\Query\JoinClause */
                $join->on(
                    'ground_vehicle_translations.language_id',
                    '=',
                    'languages.id'
                )->where('ground_vehicle_translations.vehicle_id', '=', $this->getKey());
            }
        )->get();

        return $collection->keyBy('locale_code');
    }
}
