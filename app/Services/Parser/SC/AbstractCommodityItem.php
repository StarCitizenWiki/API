<?php

declare(strict_types=1);

namespace App\Services\Parser\SC;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use JsonException;

abstract class AbstractCommodityItem
{
    protected string $filePath;

    protected Collection $item;

    protected Labels $labels;

    /**
     * @throws FileNotFoundException
     * @throws JsonException
     */
    public function __construct(string $filePath, Labels $labels)
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
     * @param  string  $description  The string to run the matches on.
     *                               Should be in the format of 'Keyword: Data Keyword: ...'
     * @param  array  $wantedMatches  Associative array mapping a Keyword to an output index on the returned array
     *                                Example: [ 'Temp. Rating' => 'temp_rating' ] would try to find 'Temp. Rating' in $description and add
     *                                the matched content to 'temp_rating' => match on the output
     */
    protected function tryExtractDataFromDescription(string $description, array $wantedMatches): array
    {
        $description = str_replace('\\n \\n', '\\n\\n', $description);

        $description = trim(str_replace('\n', "\n", $description));
        $description = str_replace(['‘', '’', '`', '´', ' '], ['\'', '\'', '\'', '\'', ' '], $description);

        $parts = explode("\n", $description);

        if (count($parts) === 1) {
            $parts = explode('\n', $parts[0]);
        }

        $withColon = collect($parts)->filter(function (string $part) {
            return preg_match('/\w:[\s| ]/u', $part) === 1;
        })->implode("\n");

        $match = preg_match_all(
            '/('.implode('|', array_keys($wantedMatches)).'):(?:\s| )?([µ\w_& (),.\-°\/%%+-]*)(?:\n|\\\n|$)/m',
            $withColon,
            $matches
        );

        if ($match === false || $match === 0) {
            if ($description === '<= PLACEHOLDER =>' || str_contains($description, '[PH]')) {
                return [];
            }

            return [
                'description' => $description,
            ];
        }

        $out = [];

        for ($i = 0, $iMax = count($matches[1]); $i < $iMax; $i++) {
            if (isset($wantedMatches[$matches[1][$i]])) {
                $value = trim($matches[2][$i]);

                $out[$wantedMatches[$matches[1][$i]]] = $value;
            }
        }

        return $out + [
            'description' => $this->getDescriptionText($description),
        ];
    }

    /**
     * Tries to remove the leading part of a description containing data
     */
    protected function getDescriptionText(string $description): string
    {
        $description = str_replace('\\n \\n', '\\n\\n', $description);

        $description = trim(str_replace('\n', "\n", $description));
        $description = str_replace(['‘', '’', '`', '´', ' '], ['\'', '\'', '\'', '\'', ' '], $description);
        $exploded = explode("\n\n", $description);

        if (count($exploded) === 1) {
            $exploded = explode('\n\n', $exploded[0]);
        }

        $exploded = array_filter($exploded, static function (string $part) {
            return preg_match('/(：|\w:[\s| ])/u', $part) !== 1;
        });

        return trim(implode("\n\n", $exploded));
    }

    protected function getName(array $attachDef, string $default): string
    {
        $key = substr($attachDef['Localization']['__Name'], 1);
        if (str_contains($key, '_Desc_')) {
            $key = str_replace('_Desc_', '_Name_', $key);
        }
        $name = $this->labels->getData()->get($key);
        $nameP = $this->labels->getData()->get($key.',P');
        $name = $this->cleanString(trim($name ?? $nameP ?? $default));

        return empty($name) ? $default : $name;
    }

    protected function getDescriptionKey(array $attachDef): string
    {
        return substr($attachDef['Localization']['__Description'], 1);
    }

    protected function getDescription(array $attachDef, string $locale = 'en'): string
    {
        switch ($locale) {
            case 'zh':
                return $this->cleanString($this->labels->getDataZh()->get($this->getDescriptionKey($attachDef), ''));

            default:
            case 'en':
                return $this->cleanString($this->labels->getData()->get($this->getDescriptionKey($attachDef), ''));
        }
    }

    protected function getManufacturer(array $attachDef, Collection $manufacturers): array
    {
        $default = [
            'name' => 'Unknown Manufacturer',
            'code' => 'UNKN',
            'uuid' => '00000000-0000-0000-0000-000000000000',
        ];

        $manufacturer = $manufacturers->get($attachDef['Manufacturer']['__ref'] ?? $attachDef['Manufacturer'], $default);

        if ($manufacturer['name'] === '@LOC_PLACEHOLDER') {
            $manufacturer = $default;
        }

        return $manufacturer;
    }

    protected function cleanString(string $string): string
    {
        $string = str_replace(['‘', '’', '`', '´'], "'", $string);
        $string = str_replace(['“', '”', '"'], '"', $string);
        $string = trim(str_replace(' ', ' ', $string));

        return preg_replace('/\s+/', ' ', $string);
    }

    protected function getUUID(): ?string
    {
        return Arr::get($this->item, 'Raw.Entity.__ref');
    }

    protected function getAttachDef(): ?array
    {
        return $this->get('SAttachableComponentParams.AttachDef');
    }

    protected function get(string $key, $default = null): mixed
    {
        return Arr::get($this->item, 'Raw.Entity.Components.'.$key, $default);
    }
}
