<?php

declare(strict_types=1);

namespace App\Models\Game;

use Database\Factories\Game\BlueprintFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Blueprint extends Model
{
    /** @use HasFactory<BlueprintFactory> */
    use HasFactory;

    use HasVersionedData;

    protected $table = 'game_blueprints';

    protected $fillable = [
        'uuid',
        'slug',
    ];

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }

    public function resolveRouteBindingQuery($query, $value, $field = null)
    {
        if ($field !== null) {
            return parent::resolveRouteBindingQuery($query, $value, $field);
        }

        return $query->when(
            Str::isUuid($value),
            fn (Builder $q) => $q->where('uuid', $value),
            fn (Builder $q) => $q->where('slug', $value),
        );
    }

    public function data(): HasMany
    {
        return $this->hasMany(BlueprintData::class);
    }
}
