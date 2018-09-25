<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Type;

use App\Models\System\Translation\AbstractTranslation as Translation;

/**
 * Vehicle Type Translations Model
 */
class TypeTranslation extends Translation
{
    protected $table = 'vehicle_type_translations';

    protected $fillable = [
        'locale_code',
        'type_id',
        'translation',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function vehicleType()
    {
        return $this->belongsTo(Type::class, 'type_id');
    }
}
