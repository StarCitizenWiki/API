<?php

declare(strict_types=1);

use App\Rules\UuidFilterValue;

it('validates UUID lists with the configured delimiter', function (): void {
    config()->set('query-builder.delimiter', '|');
    $errors = [];
    $fail = function (string $message) use (&$errors): void {
        $errors[] = $message;
    };
    $rule = new UuidFilterValue;
    $first = '550e8400-e29b-41d4-a716-446655440000';
    $second = '550e8400-e29b-41d4-a716-446655440001';

    $rule->validate('uuid', "$first|$second", $fail);
    expect($errors)->toBe([]);
    $rule->validate('uuid', "$first,$second", $fail);
    expect($errors)->toHaveCount(1);
});
