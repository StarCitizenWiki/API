<?php

declare(strict_types=1);

namespace App\Support\Formatting;

use Illuminate\Support\Str;

class FormatMissionText
{
    private const string SPAN_CLASS = 'mission-token';

    /**
     * Render mission text as safe HTML.
     */
    public static function description(?string $description, ?array $tokens = null): ?string
    {
        if ($description === null) {
            return null;
        }

        $text = preg_replace('/\R{2,}/', "\n", $description) ?? $description;
        $fragments = [];

        $text = self::replaceTokenPlaceholders($text, $tokens, $fragments);
        $text = self::replaceEm4Tags($text, $fragments);

        $html = e($text);

        foreach (array_reverse($fragments, true) as $marker => $fragment) {
            $html = str_replace(e($marker), $fragment, $html);
        }

        return nl2br($html);
    }

    /**
     * If the whole description is a single mission-token placeholder, expose each possible token value as its own rendered HTML variant.
     */
    public static function descriptionVariants(?string $description, ?array $tokens = null): ?array
    {
        if ($description === null || $tokens === null) {
            return null;
        }

        if (! preg_match('/^\[([^]]+)]$/', trim($description), $matches)) {
            return null;
        }

        $values = self::tokenValues($tokens[$matches[1]] ?? null);

        if (count($values) <= 1) {
            return null;
        }

        return array_map(
            static fn (string $value): string => self::description($value, $tokens) ?? '',
            $values,
        );
    }

    public static function format(?string $title, ?string $debugName = null): ?string
    {
        if ($title === null && $debugName === null) {
            return null;
        }

        return $title ?? ($debugName !== null ? self::formatDebugName($debugName) : null);
    }

    /**
     * @param  array<string, mixed>|null  $tokens
     * @param  array<string, string>  $fragments
     */
    private static function replaceTokenPlaceholders(string $text, ?array $tokens, array &$fragments): string
    {
        if ($tokens === null || $tokens === []) {
            return $text;
        }

        foreach ($tokens as $key => $rawValues) {
            if (! is_string($key)) {
                continue;
            }

            $values = self::tokenValues($rawValues);

            if ($values === []) {
                continue;
            }

            $placeholder = '['.$key.']';

            if (! str_contains($text, $placeholder)) {
                continue;
            }

            $marker = self::marker($fragments, 'TOKEN');
            $fragments[$marker] = self::tokenSpan($key, $values);
            $text = str_replace($placeholder, $marker, $text);
        }

        return $text;
    }

    /**
     * @param  array<string, string>  $fragments
     */
    private static function replaceEm4Tags(string $text, array &$fragments): string
    {
        return preg_replace_callback(
            '/<EM4>(.*?)<\/EM4>/s',
            static function (array $matches) use (&$fragments): string {
                $marker = self::marker($fragments, 'EM4');
                $fragments[$marker] = '<span class="'.self::SPAN_CLASS.'">'.e($matches[1]).'</span>';

                return $marker;
            },
            $text,
        ) ?? $text;
    }

    /**
     * @param  list<string>  $values
     */
    private static function tokenSpan(string $tokenKey, array $values): string
    {
        if (count($values) === 1) {
            return '<span class="'.self::SPAN_CLASS.'">'.e($values[0]).'</span>';
        }

        return sprintf(
            '<span class="%s" title="%s">%s</span>',
            self::SPAN_CLASS,
            e(implode(' / ', $values)),
            e(self::tokenLabel($tokenKey)),
        );
    }

    private static function tokenLabel(string $tokenKey): string
    {
        $parts = explode('|', $tokenKey);

        if (! isset($parts[1]) || $parts[1] === '') {
            return '['.$tokenKey.']';
        }

        $lastPart = $parts[array_key_last($parts)];
        $label = strcasecmp($lastPart, 'Address') === 0 ? $parts[0] : $parts[1];

        return '['.$label.']';
    }

    /**
     * @return list<string>
     */
    private static function tokenValues(mixed $rawValues): array
    {
        if (! is_array($rawValues)) {
            return [];
        }

        $values = [];

        foreach ($rawValues as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $value = trim((string) $value);

            if ($value !== '') {
                $values[$value] = true;
            }
        }

        return array_keys($values);
    }

    /**
     * @param  array<string, string>  $fragments
     */
    private static function marker(array $fragments, string $prefix): string
    {
        return '%%MISSION_'.$prefix.'_'.count($fragments).'%%';
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
