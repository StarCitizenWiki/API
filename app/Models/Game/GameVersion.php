<?php

declare(strict_types=1);

namespace App\Models\Game;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * Map of channel values to filesystem disk names.
     *
     * @var array<string, string>
     */
    private const CHANNEL_DISK_MAP = [
        'PTU' => 'scunpacked_ptu',
        'EPTU' => 'scunpacked_ptu',
        'TECHPREVIEW' => 'scunpacked_ptu',
    ];

    public function aliases(): HasMany
    {
        return $this->hasMany(GameVersionAlias::class);
    }

    /**
     * Resolve the filesystem disk name for this version's channel.
     */
    public function getStorageDiskName(): string
    {
        $channel = strtoupper((string) $this->channel);

        return self::CHANNEL_DISK_MAP[$channel] ?? 'scunpacked';
    }

    public function scopeRequestedOrDefault(Builder $query, ?string $code): Builder
    {
        if ($code !== null) {
            $normalizedCode = strtoupper($code);

            return $query->where(function (Builder $query) use ($normalizedCode): void {
                $query->where('code', $normalizedCode)
                    ->orWhereIn(
                        'id',
                        GameVersionAlias::query()
                            ->select('game_version_id')
                            ->where('code', $normalizedCode)
                    );
            });
        }

        return $query->where('is_default', true);
    }

    public static function resolveRequestedOrDefault(?string $code): self
    {
        if ($code !== null) {
            return static::findByCode($code, fail: true);
        }

        return static::requestedOrDefault(null)->firstOrFail();
    }

    /** Find a version by code or alias (case-insensitive). Pass fail=true to throw 404. */
    public static function findByCode(string $code, bool $fail = false): ?self
    {
        $normalizedCode = strtoupper($code);

        $version = static::query()
            ->where('code', $normalizedCode)
            ->first();

        if ($version !== null) {
            return $version;
        }

        $alias = GameVersionAlias::query()
            ->where('code', $normalizedCode)
            ->with('gameVersion')
            ->first();

        if ($alias?->gameVersion !== null) {
            return $alias->gameVersion;
        }

        if ($fail) {
            throw (new ModelNotFoundException)->setModel(static::class, [$code]);
        }

        return null;
    }

    public static function versionFamily(string $code): ?string
    {
        if (! preg_match('/^(\d+\.\d+)/', $code, $matches)) {
            return null;
        }

        return $matches[1];
    }

    public static function patchFamily(string $code): ?string
    {
        if (! preg_match('/^(\d+\.\d+\.\d+)/', $code, $matches)) {
            return null;
        }

        return $matches[1];
    }

    public function findPreviousVersionFamily(): ?self
    {
        $family = static::versionFamily($this->code);

        if ($family === null) {
            return null;
        }

        return static::query()
            ->where('code', 'NOT LIKE', "{$family}.%")
            ->where('code', 'NOT LIKE', "{$family}-%")
            ->when(
                $this->released_at !== null,
                fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                    $query->where('released_at', '<', $this->released_at)
                        ->orWhere(function (Builder $query): void {
                            $query->where('released_at', $this->released_at)
                                ->where('id', '<', $this->id);
                        });
                }),
                fn (Builder $query): Builder => $query->where('id', '<', $this->id),
            )
            ->orderByDesc('released_at')
            ->orderByDesc('id')
            ->first();
    }

    public function findPreviousVersion(): ?self
    {
        return $this->findPreviousPatchVersion() ?? $this->findPreviousMinorVersion();
    }

    public function findPreviousPatchVersion(): ?self
    {
        $patch = static::patchFamily($this->code);

        if ($patch === null) {
            return null;
        }

        return static::query()
            ->where('code', 'LIKE', "{$patch}%")
            ->where('code', '!=', $this->code)
            ->when(
                $this->released_at !== null,
                fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                    $query->where('released_at', '<', $this->released_at)
                        ->orWhere(function (Builder $query): void {
                            $query->where('released_at', $this->released_at)
                                ->where('id', '<', $this->id);
                        });
                }),
                fn (Builder $query): Builder => $query->where('id', '<', $this->id),
            )
            ->orderByDesc('released_at')
            ->orderByDesc('id')
            ->first();
    }

    public function findPreviousMinorVersion(): ?self
    {
        $family = static::versionFamily($this->code);

        if ($family === null) {
            return null;
        }

        $parts = explode('.', $family);
        $previousMinor = ((int) $parts[1]) - 1;

        if ($previousMinor < 0) {
            return null;
        }

        $prefix = "{$parts[0]}.{$previousMinor}";

        return static::query()
            ->where('code', 'LIKE', "{$prefix}.%")
            ->where('code', '!=', $this->code)
            ->when(
                $this->released_at !== null,
                fn (Builder $query): Builder => $query->where(function (Builder $query): void {
                    $query->where('released_at', '<', $this->released_at)
                        ->orWhere(function (Builder $query): void {
                            $query->where('released_at', $this->released_at)
                                ->where('id', '<', $this->id);
                        });
                }),
                fn (Builder $query): Builder => $query->where('id', '<', $this->id),
            )
            ->orderByDesc('released_at')
            ->orderByDesc('id')
            ->first();
    }
}
