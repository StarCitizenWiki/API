<?php

declare(strict_types=1);

/**
 * Static naming violation scanner for Game API resources.
 *
 * Scans resource source files via regex: no DB, no factories, no HTTP.
 * Only checks output keys (left side of => in toArray), not source data keys.
 */

/**
 * Collect all PHP files in the Game resource directory (excluding Concerns).
 */
function namingResourceFiles(): array
{
    $dir = dirname(__DIR__, 4).'/app/Http/Resources/Game';
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
    );

    foreach ($iterator as $file) {
        if ($file->getExtension() === 'php' && ! str_contains($file->getPathname(), '/Concerns/')) {
            $files[] = $file->getRealPath();
        }
    }

    sort($files);

    return $files;
}

/**
 * Extract output keys from a resource file's toArray method body.
 * Matches  'key_name' =>  patterns (single-quoted string keys only).
 *
 * @return array<int, array{key: string, file: string, line: int}>
 */
function namingExtractOutputKeys(string $filePath): array
{
    $source = file_get_contents($filePath);
    if ($source === false) {
        return [];
    }

    $lines = explode("\n", $source);
    $inToArray = false;
    $depth = 0;
    $keys = [];

    foreach ($lines as $lineNum => $line) {
        $lineIndex = $lineNum + 1;

        // Track whether we're inside a toArray() method
        if (preg_match('/function\s+toArray\s*\(/', $line)) {
            $inToArray = true;
            $depth = 0;
        }

        if (! $inToArray) {
            continue;
        }

        // Track brace depth to know when toArray ends
        $depth += substr_count($line, '{') - substr_count($line, '}');

        if ($depth <= 0 && preg_match('/}\s*$/', $line)) {
            $inToArray = false;

            continue;
        }

        // Match output keys: 'key_name' => in the return array
        if (preg_match("/^\s*'([a-zA-Z_][a-zA-Z0-9_]*)'\s*=>/", $line, $matches)) {
            // Skip match arm patterns: check if 'match (' appears in preceding 15 lines
            $isMatchArm = false;
            for ($i = max(0, $lineNum - 15); $i < $lineNum; $i++) {
                if (preg_match('/\bmatch\s*\(/', $lines[$i])) {
                    $isMatchArm = true;
                    break;
                }
            }
            if ($isMatchArm) {
                continue;
            }

            $keys[] = [
                'key' => $matches[1],
                'file' => basename(dirname($filePath)).'/'.basename($filePath),
                'line' => $lineIndex,
            ];
        }
    }

    return $keys;
}

/**
 * Check if a key follows snake_case convention.
 */
function namingIsSnakeCase(string $key): bool
{
    return preg_match('/^[a-z][a-z0-9_]*$/', $key) === 1;
}

it('resource output keys use snake_case', function (string $file): void {
    $keys = namingExtractOutputKeys($file);

    $violations = [];
    foreach ($keys as $entry) {
        if (namingIsSnakeCase($entry['key'])) {
            continue;
        }

        $violations[] = "{$entry['file']}:{$entry['line']}: '{$entry['key']}'";
    }

    expect($violations)->toBeEmpty(
        "Non-snake_case output keys found:\n  ".implode("\n  ", $violations),
    );
})->with(fn () => namingResourceFiles())->group('naming');

it('no resource uses web_link key (must be web_url)', function (): void {
    $files = namingResourceFiles();
    $violations = [];

    foreach ($files as $file) {
        $keys = namingExtractOutputKeys($file);
        foreach ($keys as $entry) {
            if ($entry['key'] === 'web_link') {
                $violations[] = "{$entry['file']}:{$entry['line']}";
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Found 'web_link' key: use 'web_url' instead:\n  ".implode("\n  ", $violations),
    );
})->group('naming');

it('no resource uses uneditable without editable companion', function (): void {
    $files = namingResourceFiles();
    $violations = [];

    foreach ($files as $file) {
        $keys = namingExtractOutputKeys($file);
        $keyList = array_column($keys, 'key');

        $hasEditable = in_array('editable', $keyList, true);

        foreach ($keys as $entry) {
            if ($entry['key'] === 'uneditable' && ! $hasEditable) {
                $violations[] = "{$entry['file']}:{$entry['line']}: 'uneditable' without 'editable'";
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Found 'uneditable' without 'editable' companion:\n  ".implode("\n  ", $violations),
    );
})->group('naming');

it('sub-type key is sub_type not bare subtype', function (): void {
    $files = namingResourceFiles();
    $violations = [];

    foreach ($files as $file) {
        $keys = namingExtractOutputKeys($file);
        $keyList = array_column($keys, 'key');

        $hasSubType = in_array('sub_type', $keyList, true);

        foreach ($keys as $entry) {
            if ($entry['key'] === 'subtype' && ! $hasSubType) {
                $violations[] = "{$entry['file']}:{$entry['line']}: 'subtype' without 'sub_type'";
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Found 'subtype' without 'sub_type' companion:\n  ".implode("\n  ", $violations),
    );
})->group('naming');

it('min/max keys use prefix_min/prefix_max or bare min/max, not minimum/maximum', function (): void {
    $files = namingResourceFiles();
    $violations = [];

    foreach ($files as $file) {
        $keys = namingExtractOutputKeys($file);
        $keyList = array_column($keys, 'key');

        $hasMin = in_array('min', $keyList, true);
        $hasMax = in_array('max', $keyList, true);
        $hasPrefixedMin = (bool) preg_grep('/_min$/', $keyList);
        $hasPrefixedMax = (bool) preg_grep('/_max$/', $keyList);

        foreach ($keys as $entry) {
            if ($entry['key'] === 'minimum' && ! $hasMin && ! $hasPrefixedMin) {
                $violations[] = "{$entry['file']}:{$entry['line']}: bare 'minimum' without 'min' or '*_min'";
            }
            if ($entry['key'] === 'maximum' && ! $hasMax && ! $hasPrefixedMax) {
                $violations[] = "{$entry['file']}:{$entry['line']}: bare 'maximum' without 'max' or '*_max'";
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Bare 'minimum'/'maximum' without canonical 'min'/'max' or '*_min'/'*_max':\n  ".implode("\n  ", $violations),
    );
})->group('naming');

it('VehicleLinkResource uses size_class', function (): void {
    $file = dirname(__DIR__, 4).'/app/Http/Resources/Game/Vehicle/VehicleLinkResource.php';
    $source = file_get_contents($file);

    expect($source)->toContain("'size_class'");
})->group('naming');
