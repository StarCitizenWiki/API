<?php

declare(strict_types=1);

namespace App\Services\Wiki;

/**
 * Extracts the {{Vehicle}} infobox params from a wiki page.
 */
final class VehicleInfoboxParser
{
    private const string TEMPLATE_START = '/\{\{Vehicle(?=\s*(?:\||}|\z))/';

    private const string TEMPLATE_END = '/^\s*}}\s*$/';

    private const string PARAM_LINE = '/^\s*\|([^=]*)=(.*)$/';

    private const string HTML_COMMENT = '/<!--.*?-->/s';

    /**
     * @param  string  $wikitext  full page wikitext
     * @return array<string, string|null> lowercased param map, null for empty
     */
    public function parse(string $wikitext): array
    {
        if (preg_match(self::TEMPLATE_START, $wikitext, $start, PREG_OFFSET_CAPTURE) !== 1) {
            return [];
        }

        $params = [];

        foreach (explode("\n", substr($wikitext, (int) $start[0][1] + strlen($start[0][0]))) as $line) {
            if (preg_match(self::TEMPLATE_END, $line) === 1) {
                break;
            }

            if (preg_match(self::PARAM_LINE, $line, $param) !== 1) {
                continue;
            }

            $value = preg_replace(self::HTML_COMMENT, '', $param[2]) ?? '';

            $params[mb_strtolower(trim($param[1]))] = trim($value) === '' ? null : trim($value);
        }

        return $params;
    }
}
