<?php

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemData extends Model
{
    protected $table = 'game_item_data';

    protected $fillable = [
        'item_id',
        'game_version_id',
        'name',
        'type',
        'sub_type',
        'classification',
        'size',
        'grade',
        'class_name',
        'manufacturer_id',
        'base_id',
        'data',
    ];

    protected $casts = [
        'size' => 'integer',
        'grade' => 'integer',
        'data' => AsCollection::class,
    ];

    protected $with = [
        'gameVersion',
        'manufacturer',
        'translations',
        'descriptionData',
    ];

    public function scopeForRequestedOrDefaultVersion(Builder $query, ?string $code = null): Builder
    {
        if ($code !== null) {
            return $query->whereHas('gameVersion', function (Builder $q) use ($code) {
                $q->whereRaw('LOWER(code) = ?', [strtolower($code)]);
            });
        }

        return $query->whereHas('gameVersion', function (Builder $q) {
            $q->where('is_default', true);
        });
    }

    public function getDescriptionDatum(string $name)
    {
        return $this->descriptionData()
            ->where('name', $name)
            ->first()?->value;
    }

    public function getDescriptionTypeAttribute()
    {
        return $this->getDescriptionDatum('Type');
    }

    public function getDescriptionManufacturerAttribute()
    {
        return $this->getDescriptionDatum('Manufacturer');
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function descriptionData(): HasMany
    {
        return $this->hasMany(ItemDescriptionData::class, 'item_id');
    }

    public function baseVariant(): BelongsTo
    {
        return $this->belongsTo(self::class, 'base_id', 'id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(self::class, 'base_id', 'id');
    }

    public function gameVersion(): BelongsTo
    {
        return $this->belongsTo(GameVersion::class, 'game_version_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(ItemTranslation::class);
    }
}
