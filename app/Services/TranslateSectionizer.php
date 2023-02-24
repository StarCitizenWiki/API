<?php

declare(strict_types=1);

namespace App\Services;

/**
 * Copied version from Extension:Translate
 * https://github.com/wikimedia/mediawiki-extensions-Translate/commit/5a0a1b1aaa47429bcd061b31c0ff2e9cd731aa49
 * TranslatablePageParser::parseSection
 */
final class TranslateSectionizer
{
    private static $i = 0;

    public static function getPlaceholder()
    {
        return self::$i++;
    }

    public static function sectionise(string $text): array
    {
        self::$i = 0;
        $flags = PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE;
        $parts = preg_split('~(^\s*|\s*\n\n\s*|\s*$)~', $text, -1, $flags);

        $sections = [];

        foreach ($parts as $_) {
            if (trim($_) !== '') {
                $sections[self::getPlaceholder()] = $_;
            }
        }

        return $sections;
    }
}
