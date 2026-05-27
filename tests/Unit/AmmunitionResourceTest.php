<?php

declare(strict_types=1);

use App\Http\Resources\Game\ItemSpecification\AmmunitionResource;
use Illuminate\Http\Request;
use OpenApi\Attributes\Schema;

it('serializes non-legacy ammunition nested keys as snake case', function (): void {
    $resource = new AmmunitionResource([
        'data' => [
            'stdItem' => [
                'Ammunition' => [
                    'DamageDropMinDistance' => ['Physical' => 0, 'Energy' => 200],
                    'DamageDropPerMeter' => ['Energy' => 0.01],
                    'DamageDropMinDamage' => ['Physical' => 10, 'Stun' => 6],
                    'BulletImpulseFalloff' => ['MinDistance' => 1, 'DropFalloff' => 0.005, 'MaxFalloff' => 0.3],
                    'BulletElectron' => ['JumpRange' => 40, 'MaximumJumps' => 2, 'ResidualChargeMultiplier' => 0.5],
                ],
            ],
        ],
    ]);

    $data = $resource->resolve(Request::create('/'));

    expect($data['damage_drop_min_distance'])->toHaveKeys(['physical', 'energy', 'total'])
        ->and($data['damage_drop_per_meter'])->toHaveKeys(['energy', 'total'])
        ->and($data['damage_drop_min_damage'])->toHaveKeys(['physical', 'stun', 'total'])
        ->and($data['bullet_impulse_falloff'])->toHaveKeys(['min_distance', 'drop_falloff', 'max_falloff'])
        ->and($data['bullet_electron'])->toHaveKeys(['jump_range', 'maximum_jumps', 'residual_charge_multiplier'])
        ->and(array_keys($data['damage_falloffs']['min_distance']))->toBe(['Physical', 'Energy']);
});

it('documents legacy falloffs unchanged and non-legacy nested keys as snake case', function (): void {
    $schemas = collect((new ReflectionClass(AmmunitionResource::class))->getAttributes(Schema::class))
        ->map(fn (ReflectionAttribute $attribute): Schema => $attribute->newInstance())
        ->keyBy('schema');

    $falloffProperties = collect($schemas->get('ammunition_damage_falloff')->properties)
        ->pluck('property')
        ->all();

    $ammunitionProperties = collect($schemas->get('ammunition')->properties)->keyBy('property');
    $nestedProperties = fn (string $property): array => collect($ammunitionProperties->get($property)->properties)
        ->pluck('property')
        ->all();

    expect($falloffProperties)->toBe(['Physical', 'Energy', 'Distortion', 'Thermal', 'Biochemical', 'Stun'])
        ->and($nestedProperties('damage_drop_min_distance'))->toBe(['physical', 'energy', 'distortion', 'thermal', 'biochemical', 'stun', 'total'])
        ->and($nestedProperties('damage_drop_per_meter'))->toBe(['physical', 'energy', 'distortion', 'thermal', 'biochemical', 'stun', 'total'])
        ->and($nestedProperties('damage_drop_min_damage'))->toBe(['physical', 'energy', 'distortion', 'thermal', 'biochemical', 'stun', 'total'])
        ->and($nestedProperties('bullet_impulse_falloff'))->toBe(['min_distance', 'drop_falloff', 'max_falloff'])
        ->and($nestedProperties('bullet_electron'))->toBe(['jump_range', 'maximum_jumps', 'residual_charge_multiplier']);
});
