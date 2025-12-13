<?php

it('loads vehicle overrides from config', function () {
    $overrides = config('game.vehicle_name_overrides');

    expect($overrides)->toBeArray()
        ->and($overrides)->toHaveKey('Aegis Retaliator')
        ->and($overrides['Aegis Retaliator'])->toBe('Retaliator Bomber');
});
