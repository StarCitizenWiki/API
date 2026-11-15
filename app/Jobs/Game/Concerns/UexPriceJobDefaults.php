<?php

declare(strict_types=1);

namespace App\Jobs\Game\Concerns;

use App\Models\Game\GameVersion;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Shared handle() prologue, throttle and failure logging for the UEX price jobs.
 *
 * Requires Batchable, FiltersUexVersions and a readonly int $gameVersionId property on the using class.
 */
trait UexPriceJobDefaults
{
    protected const int THROTTLE_MICROSECONDS = 200_000;

    /**
     * @return array{0: GameVersion, 1: array<string, string>}|null [gameVersion, versionPrefixMap]; null when the batch is cancelled or the version no longer exists
     */
    protected function resolveVersionContext(): ?array
    {
        if ($this->batch()?->cancelled()) {
            return null;
        }

        $gameVersion = GameVersion::find($this->gameVersionId);

        if ($gameVersion === null) {
            $this->reportMissingGameVersion();

            return null;
        }

        return [$gameVersion, $this->buildVersionPrefixMap($gameVersion->code)];
    }

    protected function reportMissingGameVersion(): void {}

    public function failed(Throwable $exception): void
    {
        Log::error($this->failedLogMessage(), [
            'game_version_id' => $this->gameVersionId,
            ...$this->failedLogContext(),
            'message' => $exception->getMessage(),
        ]);
    }

    abstract protected function failedLogMessage(): string;

    /**
     * @return array<string, mixed>
     */
    protected function failedLogContext(): array
    {
        return [];
    }
}
