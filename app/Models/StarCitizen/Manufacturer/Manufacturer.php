<?php declare(strict_types = 1);

namespace App\Models\StarCitizen;

use App\Traits\HasModelTranslationsTrait as HasTranslations;
use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    use HasTranslations;

    protected $fillable = [
        'cig_id',
        'name',
        'name_short',
    ];

    protected $with = [
        'manufacturers_translations',
    ];
}
