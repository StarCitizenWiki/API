<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Manufacturer;

use App\Traits\HasTranslationsTrait as Translations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Manufacturer Model
 */
class Manufacturer extends Model
{
    use VehicleRelations;
    use Translations;

    protected $fillable = [
        'cig_id',
        'name',
        'name_short',
    ];

    protected $with = [
        'translations',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function translations()
    {
        return $this->hasMany(ManufacturerTranslation::class);
    }

    /**
     * Translations Joined with Languages
     *
     * @return \Illuminate\Support\Collection
     */
    public function translationsCollection(): Collection
    {
        $collection = DB::table('manufacturer_translations')->select('*')->rightJoin(
            'languages',
            function ($join) {
                /** @var $join \Illuminate\Database\Query\JoinClause */
                $join->on(
                    'manufacturer_translations.locale_code',
                    '=',
                    'languages.locale_code'
                )->where('manufacturer_translations.manufacturer_id', '=', $this->getKey());
            }
        )->get();

        return $collection->keyBy('locale_code');
    }
}
