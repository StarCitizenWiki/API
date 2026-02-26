<?php

declare(strict_types=1);

use App\Http\Requests\CreateTokenRequest;

it('name is required', function (): void {
    $request = new CreateTokenRequest;

    $validator = validator([], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue()
        ->and($validator->errors()->first('name'))->toBe('A token name is required.');
});

it('name must be a string', function (): void {
    $request = new CreateTokenRequest;

    $validator = validator(['name' => 123], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue()
        ->and($validator->errors()->first('name'))->toBe('The token name must be a string.');
});

it('name must not exceed 255 characters', function (): void {
    $request = new CreateTokenRequest;

    $validator = validator(['name' => str_repeat('a', 256)], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue()
        ->and($validator->errors()->first('name'))->toBe('The token name must not exceed 255 characters.');
});

it('valid name passes validation', function (): void {
    $request = new CreateTokenRequest;

    $validator = validator(['name' => 'My Token'], $request->rules(), $request->messages());

    expect($validator->fails())->toBeFalse()
        ->and($validator->errors()->isEmpty())->toBeTrue();
});

it('custom error messages are returned', function (): void {
    $request = new CreateTokenRequest;

    expect($request->messages())->toBe([
        'name.required' => 'A token name is required.',
        'name.string' => 'The token name must be a string.',
        'name.max' => 'The token name must not exceed 255 characters.',
    ]);
});
