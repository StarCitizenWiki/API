<?php

use App\Http\Requests\CreateTokenRequest;

test('name is required', function () {
    $request = new CreateTokenRequest;

    $validator = validator([], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue()
        ->and($validator->errors()->first('name'))->toBe('A token name is required.');
});

test('name must be a string', function () {
    $request = new CreateTokenRequest;

    $validator = validator(['name' => 123], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue()
        ->and($validator->errors()->first('name'))->toBe('The token name must be a string.');
});

test('name must not exceed 255 characters', function () {
    $request = new CreateTokenRequest;

    $validator = validator(['name' => str_repeat('a', 256)], $request->rules(), $request->messages());

    expect($validator->fails())->toBeTrue()
        ->and($validator->errors()->has('name'))->toBeTrue()
        ->and($validator->errors()->first('name'))->toBe('The token name must not exceed 255 characters.');
});

test('valid name passes validation', function () {
    $request = new CreateTokenRequest;

    $validator = validator(['name' => 'My Token'], $request->rules(), $request->messages());

    expect($validator->fails())->toBeFalse()
        ->and($validator->errors()->isEmpty())->toBeTrue();
});

test('custom error messages are returned', function () {
    $request = new CreateTokenRequest;

    expect($request->messages())->toBe([
        'name.required' => 'A token name is required.',
        'name.string' => 'The token name must be a string.',
        'name.max' => 'The token name must not exceed 255 characters.',
    ]);
});
