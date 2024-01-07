<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\Vehicle\Vehicle;

use App\Traits\HasModelChangelogTrait as ModelChangelog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ship Translations Model
 */
class VehicleSku extends Model
{
    use ModelChangelog;

    protected $fillable = [
        'vehicle_id',
        'title',
        'price',
        'available',
        'cig_id',
    ];

    /**
     * @return BelongsTo Ships
     */
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
