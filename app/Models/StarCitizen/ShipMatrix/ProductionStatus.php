<?php

declare(strict_types=1);

namespace App\Models\StarCitizen\ShipMatrix;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class ProductionStatus extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $table = 'shipmatrix_production_statuses';

    public array $translatable = ['translation'];

    protected $fillable = [
        'slug',
        'translation',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
