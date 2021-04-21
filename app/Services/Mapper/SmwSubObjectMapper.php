<?php

declare(strict_types=1);

namespace App\Services\Mapper;

final class SmwSubObjectMapper
{
    private static string $format = "{{#subobject:%s%s}}";

    /**
     * Maps an array of key value pairs to a wikitext SemanticMediaWiki subobject
     *
     * @param array $data The key value pairs to map
     * @param string $separator The separator to use after each key value pair
     * @param array $indexContentMaxLengths An optional array containing ['key' => 'Max length of value'].
     *                                      If set will pad all values to the same length
     * @param string $id Id of the subobject
     * @return string
     */
    public static function map(array $data, string $separator = ' ', array $indexContentMaxLengths = [], string $id = ''): string
    {
        $string = collect($data)->map(function ($item, $key) use ($indexContentMaxLengths) {
            $item = trim((string)$item);

            return sprintf('|%s=%s', $key, str_pad($item, $indexContentMaxLengths[$key] ?? 0));
        })
            ->implode($separator);

        return sprintf(self::$format, $id, rtrim($string));
    }

    /**
     * @param array $data The key value pairs to map
     * @param array $indexContentMaxLengths An optional array containing ['key' => 'Max length of value'].
     *                                      If set will pad all values to the same length
     * @return string
     * @see SmwSubObjectMapper::map()
     */
    public static function mapInline(array $data, array $indexContentMaxLengths = []): string
    {
        return self::map($data, ' ', $indexContentMaxLengths);
    }

    /**
     * @param array $data The key value pairs to map
     * @param array $indexContentMaxLengths An optional array containing ['key' => 'Max length of value'].
     *                                      If set will pad all values to the same length
     * @return string
     * @see SmwSubObjectMapper::map()
     */
    public static function mapBlock(array $data, array $indexContentMaxLengths = []): string
    {
        return self::map($data, "\n", $indexContentMaxLengths);
    }
}
