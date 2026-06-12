<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

class Vehicle extends Model
{
    use HasFactory;
    use HasTranslations;
    use HasVersionedData;

    public array $translatable = ['translation'];

    protected $table = 'game_vehicles';

    protected $fillable = [
        'uuid',
        'slug',
        'display_name_slug',
        'translation',
    ];

    protected $casts = [
        'images' => 'array',
    ];

    public function data(): HasMany
    {
        return $this->hasMany(VehicleData::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class, 'uuid', 'uuid');
    }
}
