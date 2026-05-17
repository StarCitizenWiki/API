<?php

declare(strict_types=1);

namespace App\Jobs\Game\Concerns;

trait FiltersUexVersions
{
    /**
     * @return array<string, string> apiVersionPrefix => dbVersionCode
     */
    private function buildVersionPrefixMap(string $currentVersionCode): array
    {
        $map = [];

        $map[$this->extractMajorMinor($currentVersionCode)] = $currentVersionCode;

        if ($this->previousVersionCode !== null) {
            $map[$this->extractMajorMinor($this->previousVersionCode)] = $this->previousVersionCode;
        }

        return $map;
    }

    private function extractMajorMinor(string $version): string
    {
        if (preg_match('/^(\d+\.\d+)/', $version, $matches)) {
            return $matches[1];
        }

        return $version;
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
