<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

class AbstractCommodityItem
{
    protected function tryExtractDataFromDescription(string $description, array $wantedMatches): array
    {
        $match = preg_match_all(
            sprintf('/^(%s):(?:\s| )?([\w_&\ \(\),\.\/\\\]*)$/m', implode('|', array_keys($wantedMatches))),
            $description,
            $matches
        );

        if ($match === false || $match === 0) {
            return [];
        }

        $out = [];

        for ($i = 0, $iMax = count($matches[1]); $i < $iMax; $i++) {
            if (isset($wantedMatches[$matches[1][$i]])) {
                $value = trim($matches[2][$i]);

                $out[$wantedMatches[$matches[1][$i]]] = $value;
            }
        }

        $exploded = explode("\n\n", $description);
        $exploded = array_pop($exploded);

        return $out + [
                'description' => $exploded,
            ];
    }

    protected function getManufacturer(array $item): string
    {
        $manufacturer = trim($item['Manufacturer']['Name'] ?? 'Unknown Manufacturer');
        if ($manufacturer === '@LOC_PLACEHOLDER') {
            $manufacturer = 'Unknown Manufacturer';
        }

        return $manufacturer;
    }
}
