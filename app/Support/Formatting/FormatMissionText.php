<?php

declare(strict_types=1);

namespace App\Support\Formatting;

use Illuminate\Support\Str;

class FormatMissionText
{
    public static function description(?string $description): ?string
    {
        if ($description === null) {
            return null;
        }

        $description = preg_replace('/\R{2,}/', "\n", $description);

        $description = preg_replace_callback(
            '/<EM4>(.*?)<\/EM4>/s',
            static function (array $matches): string {
                return '%%EM4_SPAN_START%%'.e($matches[1]).'%%EM4_SPAN_END%%';
            },
            $description,
        );

        $description = self::replaceTemplateTokens($description);

        $description = self::escapePreservingSpans($description);

        return nl2br($description);
    }

    /**
     * Escape HTML entities while preserving our injected <span> tags.
     */
    private static function escapePreservingSpans(string $html): string
    {
        $spanPlaceholders = [];
        $html = preg_replace_callback(
            '/%%EM4_SPAN_START%%(.*?)%%EM4_SPAN_END%%/s',
            static function (array $matches) use (&$spanPlaceholders): string {
                $key = '%%SPAN_'.count($spanPlaceholders).'%%';
                $spanPlaceholders[$key] = '<span class="text-secondary font-mono">'.$matches[1].'</span>';

                return $key;
            },
            $html,
        );

        $bracketPlaceholders = [];
        $html = preg_replace_callback(
            '/\[([^\]]*)\]/',
            static function (array $matches) use (&$bracketPlaceholders): string {
                $key = '%%BRACKET_'.count($bracketPlaceholders).'%%';
                $bracketPlaceholders[$key] = '<span class="text-secondary font-mono">['.e($matches[1]).']</span>';

                return $key;
            },
            $html,
        );

        $html = e($html);

        foreach ($bracketPlaceholders as $key => $value) {
            $html = str_replace(e($key), $value, $html);
        }

        foreach ($spanPlaceholders as $key => $value) {
            $html = str_replace(e($key), $value, $html);
        }

        return $html;
    }

    public static function format(?string $title, ?string $debugName = null): ?string
    {
        if ($title === null && $debugName === null) {
            return null;
        }

        if ($title !== null && ! self::containsTemplateToken($title)) {
            return $title;
        }

        if ($title !== null) {
            $replaced = self::replaceTemplateTokens($title);

            if (self::isReadableProse($replaced)) {
                return $replaced;
            }
        }

        if ($title !== null && self::isEntirelyTemplate($title)) {
            $key = self::extractTemplateKey($title);

            if ($key !== null) {
                return self::formatKeyAsTitle($key);
            }
        }

        if ($debugName !== null) {
            return self::formatDebugName($debugName);
        }

        return $title;
    }

    private static function containsTemplateToken(string $title): bool
    {
        return str_contains($title, '~mission(');
    }

    private static function replaceTemplateTokens(string $text): string
    {
        return (string) preg_replace_callback(
            '/~mission\(([^)]+)\)/',
            static function (array $matches): string {
                $inner = $matches[1];
                $label = str_contains($inner, '|')
                    ? Str::after($inner, '|')
                    : $inner;

                return '['.Str::headline($label).']';
            },
            $text,
        );
    }

    private static function isReadableProse(string $text): bool
    {
        $trimmed = trim($text);

        if ($trimmed === '' || ! preg_match('/[a-zA-Z]{2,}/', $trimmed)) {
            return false;
        }

        $withoutBrackets = trim((string) preg_replace('/\[[^\]]*\]/', '', $trimmed));

        return $withoutBrackets !== '';
    }

    private static function isEntirelyTemplate(string $title): bool
    {
        $stripped = self::stripTemplateTokens($title);

        return trim($stripped) === '';
    }

    private static function stripTemplateTokens(string $title): string
    {
        return (string) preg_replace('/~mission\([^)]*\)/', '', $title);
    }

    private static function extractTemplateKey(string $title): ?string
    {
        if (! preg_match('/~mission\((?:[^|]*\|)?([^)]+)\)/', $title, $matches)) {
            return null;
        }

        return $matches[1];
    }

    private static function formatKeyAsTitle(string $key): string
    {
        $cleaned = Str::replaceLast('TitleVeryHard', 'Very Hard', $key);
        $cleaned = Str::replaceLast('TitleVeryEasy', 'Very Easy', $cleaned);
        $cleaned = Str::replaceLast('TitleSuper', 'Super Hard', $cleaned);
        $cleaned = Str::replaceLast('TitleHard', 'Hard', $cleaned);
        $cleaned = Str::replaceLast('TitleEasy', 'Easy', $cleaned);
        $cleaned = Str::replaceLast('TitleMedium', 'Medium', $cleaned);
        $cleaned = Str::replaceLast('TitleIntro', 'Intro', $cleaned);
        $cleaned = Str::replaceLast('Title', '', $cleaned);

        return self::headline($cleaned);
    }

    private static function formatDebugName(string $debugName): string
    {
        $parts = explode('_', $debugName);

        if (count($parts) < 2) {
            return self::headline($debugName);
        }

        $tail = self::popDifficultySuffix($parts);
        $difficulty = $tail['difficulty'];
        $parts = $tail['parts'];

        $missionBase = implode(' ', array_slice($parts, 1));

        $segments = array_filter([
            self::headline($missionBase),
            $difficulty,
        ]);

        return implode(' - ', $segments) ?: self::headline($debugName);
    }

    /**
     * @param  array<int, string>  $parts
     * @return array{parts: array<int, string>, difficulty: ?string}
     */
    private static function popDifficultySuffix(array $parts): array
    {
        $singlePartDifficulties = [
            'easy' => 'Easy',
            'hard' => 'Hard',
            'medium' => 'Medium',
            'super' => 'Super',
            'intro' => 'Intro',
        ];

        $last = strtolower(end($parts));

        if (isset($singlePartDifficulties[$last])) {
            array_pop($parts);

            return ['parts' => $parts, 'difficulty' => $singlePartDifficulties[$last]];
        }

        $count = count($parts);

        if ($count >= 2) {
            $lastTwo = strtolower($parts[$count - 2].'_'.$parts[$count - 1]);

            if ($lastTwo === 'very_hard') {
                array_pop($parts);
                array_pop($parts);

                return ['parts' => $parts, 'difficulty' => 'Very Hard'];
            }

            if ($lastTwo === 'very_easy') {
                array_pop($parts);
                array_pop($parts);

                return ['parts' => $parts, 'difficulty' => 'Very Easy'];
            }
        }

        return ['parts' => $parts, 'difficulty' => null];
    }

    private static function headline(string $value): string
    {
        $split = preg_replace('/(?<=[a-z])(?=[A-Z])/', ' ', $value);

        return Str::headline($split);
    }
}
