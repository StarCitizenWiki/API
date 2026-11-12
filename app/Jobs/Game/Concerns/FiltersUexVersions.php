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
            $previousPrefix = GameVersion::versionFamily($this->previousVersionCode);

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

        return $this->resolveDbVersionCode($apiVersion, $versionPrefixMap) !== null;
    }

    /**
     * @param  array<string, string>  $versionPrefixMap
     */
    private function resolveDbVersionCode(?string $apiVersion, array $versionPrefixMap): ?string
    {
        if ($apiVersion === null) {
            return null;
        }

        if (! preg_match('/^(\d+\.\d+)(?=\.|-|$)/', $apiVersion, $matches)) {
            return null;
        }

        return $versionPrefixMap[$matches[1]] ?? null;
    }
}
