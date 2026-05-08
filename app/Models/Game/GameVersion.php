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

    /** Find a version by code (case-insensitive). Pass fail=true to throw 404. */
    public static function findByCode(string $code, bool $fail = false): ?self
    {
        return static::where('code', strtoupper($code))->{$fail ? 'firstOrFail' : 'first'}();
    }

    public function findPreviousVersion(): ?self
    {
        return $this->findPreviousPatchVersion() ?? $this->findPreviousMinorVersion();
    }

    public function findPreviousPatchVersion(): ?self
    {
        if (! preg_match('/^(\d+\.\d+\.\d+)/', $this->code, $matches)) {
            return null;
        }

        return static::query()
            ->where('code', 'LIKE', "{$matches[1]}%")
            ->where('code', '!=', $this->code)
            ->orderByDesc('released_at')
            ->first();
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
