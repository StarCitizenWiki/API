<?php

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class GameVersion extends Model
{
    protected $fillable = [
        'code',
        'channel',
        'released_at',
        'is_default',
    ];

    protected $casts = [
        'released_at' => 'datetime',
        'is_default' => 'boolean',
    ];

    public function scopeRequestedOrDefault(Builder $query, ?string $code): Builder
    {
        if ($code !== null) {
            return $query->whereRaw('LOWER(code) = ?', [strtolower($code)]);
        }

        return $query->where('is_default', true);
    }

    public static function resolveRequestedOrDefault(?string $code): self
    {
        return static::requestedOrDefault($code)->firstOrFail();
    }
}
