<?php declare(strict_types = 1);

namespace App\Models\Api\StarCitizen\Vehicle\Vehicle;

use App\Models\System\Translation\AbstractTranslation as Translation;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ship Translations Model
 */
class VehicleTranslation extends Translation
{
    protected $fillable = [
        'locale_code',
        'vehicle_id',
        'translation',
    ];

    /**
     * @return BelongsTo Ships
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
