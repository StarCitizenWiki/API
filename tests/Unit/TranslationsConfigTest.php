<?php

declare(strict_types=1);

use Tests\TestCase;

uses(TestCase::class);

it('points the german translations source to the live ini file', function (): void {
    $expectedPath = storage_path('app/api/StarCitizenDeutsch/live/global.ini');

    expect(config('translations.sources.de_DE'))->toBe($expectedPath);
    expect(is_file($expectedPath))->toBeTrue();
});
