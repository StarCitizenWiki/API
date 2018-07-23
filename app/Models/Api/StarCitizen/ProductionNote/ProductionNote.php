<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\ProductionNote;

use App\Traits\HasTranslationsTrait as Translations;
use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Production Note Model
 */
class ProductionNote extends Model
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
        return $this->hasMany(ProductionNoteTranslation::class);
    }

    /**
     * Translations Joined with Languages
     *
     * @return \Illuminate\Support\Collection
     */
    public function translationsCollection(): Collection
    {
        $collection = DB::table('production_note_translations')->select('*')->rightJoin(
            'languages',
            function ($join) {
                /** @var $join \Illuminate\Database\Query\JoinClause */
                $join->on(
                    'production_note_translations.locale_code',
                    '=',
                    'languages.locale_code'
                )->where('production_note_translations.production_note_id', '=', $this->getKey());
            }
        )->get();

        return $collection->keyBy('locale_code');
    }
}
