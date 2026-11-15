<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\Game\GameVersion;
use Illuminate\Support\Facades\Storage;
use JsonException;

use function Laravel\Prompts\select;

trait PromptsForGameVersion
{
    /**
     * Prompt for the missing {version} argument shared by game data imports.
     */
    protected function promptForMissingArgumentsUsing(): array
    {
        return [
            'version' => fn (): string => $this->promptForGameVersionCode() ?? '',
        ];
    }

    /**
     * Prompt for a game version code, or null when no versions exist.
     */
    protected function promptForGameVersionCode(): ?string
    {
        $options = GameVersion::query()
            ->orderByRaw('released_at DESC NULLS FIRST')
            ->orderBy('code')
            ->pluck('code', 'code')
            ->toArray();

        if ($options === []) {
            $this->error('No game versions exist. Please create one before importing.');

            return null;
        }

        return select(
            label: 'Select game version to import',
            options: $options
        );
    }

    /**
     * Resolve the game version from the {version} argument or the {--game-version}
     * option, falling back to the default version when neither carries a code.
     * An explicitly passed code overrides both. Emits one shared error and
     * returns null when resolution fails.
     */
    protected function resolveGameVersionFromOptionOrArgument(?string $versionCode = null): ?GameVersion
    {
        $versionCode ??= $this->hasOption('game-version') ? $this->option('game-version') : null;

        if (! is_string($versionCode) || $versionCode === '') {
            $versionCode = $this->hasArgument('version') ? $this->argument('version') : null;
        }

        $versionCode = is_string($versionCode) && $versionCode !== '' ? $versionCode : null;

        $gameVersion = GameVersion::query()
            ->requestedOrDefault($versionCode)
            ->first();

        if ($gameVersion === null) {
            $this->error(sprintf('Game version "%s" does not exist. Please create it first.', $versionCode ?? 'default'));
        }

        return $gameVersion;
    }

    /**
     * Read and decode a JSON payload from a scunpacked storage disk.
     *
     * @param  GameVersion|string  $disk  GameVersion model or storage disk name
     */
    protected function readScunpackedJson(GameVersion|string $disk, string $path): ?array
    {
        $diskName = $disk instanceof GameVersion ? $disk->getStorageDiskName() : $disk;

        if (Storage::disk($diskName)->missing($path)) {
            $this->error(sprintf('%s not found in scunpacked storage.', $path));

            return null;
        }

        try {
            $payload = json_decode(Storage::disk($diskName)->get($path), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            $this->error(sprintf('Failed to decode %s: %s', $path, $exception->getMessage()));

            return null;
        }

        if (! is_array($payload)) {
            $this->error(sprintf('%s must contain a JSON array or object.', $path));

            return null;
        }

        return $payload;
    }
}
