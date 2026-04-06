<?php

declare(strict_types=1);

use App\Traits\NormalizesBooleanFilters;

beforeEach(function (): void {
    $this->dummy = new class
    {
        use NormalizesBooleanFilters;

        public function test(mixed $value): ?bool
        {
            return $this->normalizeBoolean($value);
        }
    };
});

it('accepts boolean true/false', function (): void {
    expect($this->dummy->test(true))->toBeTrue();
    expect($this->dummy->test(false))->toBeFalse();
});

it('accepts integer 1/0', function (): void {
    expect($this->dummy->test(1))->toBeTrue();
    expect($this->dummy->test(0))->toBeFalse();
});

it('accepts string 1/0', function (): void {
    expect($this->dummy->test('1'))->toBeTrue();
    expect($this->dummy->test('0'))->toBeFalse();
});

it('accepts string true/false case-insensitive', function (): void {
    expect($this->dummy->test('true'))->toBeTrue();
    expect($this->dummy->test('TRUE'))->toBeTrue();
    expect($this->dummy->test('True'))->toBeTrue();
    expect($this->dummy->test('false'))->toBeFalse();
    expect($this->dummy->test('FALSE'))->toBeFalse();
    expect($this->dummy->test('False'))->toBeFalse();
});

it('handles trimmed whitespace', function (): void {
    expect($this->dummy->test('  true  '))->toBeTrue();
    expect($this->dummy->test("  \tfalse\n"))->toBeFalse();
});

it('returns null for invalid values', function (): void {
    expect($this->dummy->test(null))->toBeNull();
    expect($this->dummy->test(''))->toBeNull();
    expect($this->dummy->test('yes'))->toBeNull();
    expect($this->dummy->test('no'))->toBeNull();
    expect($this->dummy->test('invalid'))->toBeNull();
    expect($this->dummy->test([]))->toBeNull();
    expect($this->dummy->test(new stdClass))->toBeNull();
});

it('returns null for string 2', function (): void {
    expect($this->dummy->test('2'))->toBeNull();
});

it('returns null for integer 2', function (): void {
    expect($this->dummy->test(2))->toBeNull();
});
