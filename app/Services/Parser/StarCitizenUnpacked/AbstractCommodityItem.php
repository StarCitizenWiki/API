<?php

declare(strict_types=1);

namespace App\Services\Parser\StarCitizenUnpacked;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

abstract class AbstractCommodityItem
{
    protected string $filePath;
    protected Collection $item;

    protected Collection $labels;

    /**
     * @param string $filePath
     * @param Collection $labels
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct(string $filePath, Collection $labels)
    {
        $this->filePath = $filePath;
        $item = File::get($filePath);
        $this->item = collect(json_decode($item, true, 512, JSON_THROW_ON_ERROR));
        $this->labels = $labels;
    }

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
            '/(' . implode('|', array_keys($wantedMatches)) . '):(?:\s| )?([µ\w_&\ \(\),\.\-\°\/\\%%]*)(?:\\n|\n|\\\n|$)/m',
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

        if (count($exploded) === 1) {
            $exploded = explode('\n\n', $exploded[0]);
        }

        $exploded = array_filter($exploded, function (string $part) {
            return !str_contains($part, ':');
        });

        $exploded = join("\n\n", $exploded);

        $exploded = str_replace(['’', '`', '´', ' '], ['\'', '\'', '\'', ' '], trim($exploded ?? ''));

        return $out + [
                'description' => trim(str_replace(["\n", '\n'], "\n", $exploded)),
            ];
    }

    protected function getName(array $attachDef, string $default): string
    {
        $name = $this->labels->get(substr($attachDef['Localization']['Name'], 1));
        $name = $this->cleanString(trim($name ?? $default));
        return empty($name) ? $default : $name;
    }

    protected function getDescriptionKey(array $attachDef): string
    {
        return substr($attachDef['Localization']['Description'], 1);
    }

    protected function getDescription(array $attachDef): string
    {
        return $this->cleanString($this->labels->get($this->getDescriptionKey($attachDef), ''));
    }

    protected function getManufacturer(array $attachDef, Collection $manufacturers): string
    {
        $manufacturer = $manufacturers->get($attachDef['Manufacturer'], ['name' => 'Unknown Manufacturer'])['name'];
        if ($manufacturer === '@LOC_PLACEHOLDER') {
            $manufacturer = 'Unknown Manufacturer';
        }

        return $manufacturer;
    }

    protected function cleanString(string $string): string
    {
        $string = str_replace(['’', '`', '´'], "'", $string);
        $string = str_replace(['“', '”', '"'], '"', $string);
        $string = trim(str_replace(' ', ' ', $string));
        return preg_replace('/\s+/', ' ', $string);
    }

    protected function getUUID(): ?string {
        return Arr::get($this->item, 'Raw.Entity.__ref');
    }

    protected function getAttachDef(): ?array {
        return $this->get('SAttachableComponentParams.AttachDef');
    }

    protected function get(string $key, $default = null): mixed {
        return Arr::get($this->item, 'Raw.Entity.Components.' . $key, $default);
    }
}
