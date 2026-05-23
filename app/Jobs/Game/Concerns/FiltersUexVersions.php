<?php

declare(strict_types=1);

namespace App\Jobs\Game\Concerns;

use App\Models\Game\GameVersion;

trait FiltersUexVersions
{
    /**
     * @return array<string, string> apiVersionPrefix => dbVersionCode
     */
    private function buildVersionPrefixMap(string $currentVersionCode): array
    {
        $map = [];

        $currentPrefix = GameVersion::versionFamily($currentVersionCode);
        $map[$currentPrefix] = $currentVersionCode;

        if ($this->previousVersionCode !== null) {
            $previousPrefix = GameVersion::patchFamily($this->previousVersionCode);

            if ($previousPrefix !== null && ! array_key_exists($previousPrefix, $map)) {
                $map[$previousPrefix] = $this->previousVersionCode;
            }
        }

        return $map;
    }

    /**
     * @param  array<string, string>  $versionPrefixMap
     */
    private function matchesKnownVersion(?string $apiVersion, array $versionPrefixMap): bool
    {
        if ($apiVersion === null) {
            return false;
        }

        return array_any($versionPrefixMap, fn ($_, $prefix) => str_starts_with($apiVersion, $prefix));
    }

    /**
     * @param  array<string, string>  $versionPrefixMap
     */
    private function resolveDbVersionCode(?string $apiVersion, array $versionPrefixMap): ?string
    {
        if ($apiVersion === null) {
            return null;
        }

        return array_find($versionPrefixMap, fn ($dbCode, $prefix) => str_starts_with($apiVersion, $prefix));
    }
}
