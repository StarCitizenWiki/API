<?php declare(strict_types = 1);

namespace App\Models\StarCitizen\ProductionStatus;

use App\Traits\HasModelTranslationsTrait as HasTranslations;
use Illuminate\Database\Eloquent\Model;

class ProductionStatus extends Model
{
    use HasTranslations;

    protected $with = [
        'production_statuses_translations',
    ];

    public function ships()
    {
        return $this->hasMany('App\Models\StarCitizen\Vehicle\Ship\Ship');
    }
}
