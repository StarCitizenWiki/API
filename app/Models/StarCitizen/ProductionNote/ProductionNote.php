<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ProductionNote;

use App\Traits\HasVehicleRelationsTrait as VehicleRelations;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Production Note Model
 */
class ProductionNote extends Model
{
    use HasFactory;
    use VehicleRelations;

    public $timestamps = false;

    protected $fillable = [
        'content_hash',
    ];

    protected $with = [
        'translations',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ProductionNoteTranslation::class);
    }
}
