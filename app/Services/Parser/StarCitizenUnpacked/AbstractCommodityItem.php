<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

abstract class AbstractCommodityItem
{
    abstract public function getData();

    /**
     * Tries to do some regex magic to extract information from a string
     *
     * @param string $description The string to run the matches on.
     * Should be in the format of 'Keyword: Data Keyword: ...'
     * @param array $wantedMatches Associative array mapping a Keyword to an output index on the returned array
     * Example: [ 'Temp. Rating' => 'temp_rating' ] would try to find 'Temp. Rating' in $description and add
     * the matched content to 'temp_rating' => match on the output
     *
     * @return array
     */
    protected function tryExtractDataFromDescription(string $description, array $wantedMatches): array
    {
        $match = preg_match_all(
            sprintf('/^(%s):(?:\s| )?([µ\w_&\ \(\),\.\-\°\/\\\\%%]*)$/m', implode('|', array_keys($wantedMatches))),
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

        $exploded = str_replace(['’', '`', '´', ' '], ['\'', '\'', '\'', ' '], trim($exploded ?? ''));

        return $out + [
                'description' => trim($exploded),
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
