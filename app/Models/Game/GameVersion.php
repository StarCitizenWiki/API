<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameVersion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'channel',
        'released_at',
        'is_default',
        'is_hidden',
    ];

    protected $casts = [
        'released_at' => 'datetime',
        'is_default' => 'boolean',
        'is_hidden' => 'boolean',
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

    public function findPreviousMinorVersion(): ?self
    {
        if (! preg_match('/^(\d+)\.(\d+)/', $this->code, $matches)) {
            return null;
        }

        $major = (int) $matches[1];
        $previousMinor = ((int) $matches[2]) - 1;

        if ($previousMinor < 0) {
            return null;
        }

        $prefix = "{$major}.{$previousMinor}";

        return static::query()
            ->where('code', 'LIKE', "{$prefix}.%")
            ->where('code', '!=', $this->code)
            ->orderByDesc('released_at')
            ->first();
    }
}
