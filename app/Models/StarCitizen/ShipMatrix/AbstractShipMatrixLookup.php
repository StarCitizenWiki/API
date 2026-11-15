<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

abstract class AbstractShipMatrixLookup extends Model
{
    use HasFactory;
    use HasTranslations;

    public array $translatable = ['translation'];

    protected $fillable = ['slug', 'translation'];
}
