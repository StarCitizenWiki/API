<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Type;

use App\Traits\HasTranslationsTrait as Translations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Vehicle Type Model
 */
class VehicleType extends Model
{
    use VehicleRelations;
    use Translations;

    public $timestamps = false;
    protected $with = [
        'translations',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(VehicleTypeTranslation::class);
    }

    /**
     * Translations Joined with Languages
     *
     * @return \Illuminate\Support\Collection
     */
    public function translationsCollection(): Collection
    {
        $collection = DB::table('vehicle_type_translations')->select('*')->rightJoin(
            'languages',
            function ($join) {
                /** @var $join \Illuminate\Database\Query\JoinClause */
                $join->on(
                    'vehicle_type_translations.locale_code',
                    '=',
                    'languages.locale_code'
                )->where('vehicle_type_translations.vehicle_type_id', '=', $this->getKey());
            }
        )->get();

        return $collection->keyBy('locale_code');
    }
}
