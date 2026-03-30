<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

afterEach(function (): void {
    DB::table('jobs')->truncate();
    DB::table('failed_jobs')->truncate();
});

it('displays dashboard successfully', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    $response = $this->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertSuccessful();
    $response->assertViewIs('admin.dashboard.index');
});

it('displays total queued jobs count', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'Test Job']),
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => time(),
        'created_at' => time(),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertSuccessful();
    $stats = $response->viewData('stats');

    expect($stats)->toHaveKey('totalJobs');
    expect($stats['totalJobs'])->toBe(1);
});

it('displays total failed jobs count', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    DB::table('failed_jobs')->insert([
        'uuid' => (string) Str::uuid(),
        'connection' => 'database',
        'queue' => 'default',
        'payload' => json_encode(['displayName' => 'Test Job']),
        'exception' => 'Test exception',
        'failed_at' => now(),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertSuccessful();
    $stats = $response->viewData('stats');

    expect($stats)->toHaveKey('failedJobs');
    expect($stats['failedJobs'])->toBe(1);
});

it('displays queued jobs queue breakdown', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    DB::table('jobs')->insert([
        [
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'Default Job']),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ],
        [
            'queue' => 'expensive',
            'payload' => json_encode(['displayName' => 'Expensive Job 1']),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ],
        [
            'queue' => 'expensive',
            'payload' => json_encode(['displayName' => 'Expensive Job 2']),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ],
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertSuccessful();
    $stats = $response->viewData('stats');

    expect($stats)->toHaveKey('queuedBreakdown');
    $breakdown = $stats['queuedBreakdown'];
    expect($breakdown)->toBeArray();
    expect($breakdown)->toHaveCount(2);
    expect($breakdown['default'])->toBe(1);
    expect($breakdown['expensive'])->toBe(2);
});

it('displays failed jobs queue breakdown', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    DB::table('failed_jobs')->insert([
        [
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'Default Job']),
            'exception' => 'Exception 1',
            'failed_at' => now(),
        ],
        [
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'Default Job']),
            'exception' => 'Exception 2',
            'failed_at' => now(),
        ],
        [
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'expensive',
            'payload' => json_encode(['displayName' => 'Expensive Job']),
            'exception' => 'Exception 3',
            'failed_at' => now(),
        ],
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertSuccessful();
    $stats = $response->viewData('stats');

    expect($stats)->toHaveKey('failedBreakdown');
    $breakdown = $stats['failedBreakdown'];
    expect($breakdown)->toBeArray();
    expect($breakdown)->toHaveCount(2);
    expect($breakdown['default'])->toBe(2);
    expect($breakdown['expensive'])->toBe(1);
});

it('orders queue breakdowns by count descending', function (): void {
    $admin = User::factory()->create(['is_admin' => true]);

    DB::table('jobs')->insert([
        ['queue' => 'high', 'payload' => json_encode(['displayName' => 'Job']), 'attempts' => 0, 'reserved_at' => null, 'available_at' => time(), 'created_at' => time()],
        ['queue' => 'high', 'payload' => json_encode(['displayName' => 'Job']), 'attempts' => 0, 'reserved_at' => null, 'available_at' => time(), 'created_at' => time()],
        ['queue' => 'high', 'payload' => json_encode(['displayName' => 'Job']), 'attempts' => 0, 'reserved_at' => null, 'available_at' => time(), 'created_at' => time()],
        ['queue' => 'medium', 'payload' => json_encode(['displayName' => 'Job']), 'attempts' => 0, 'reserved_at' => null, 'available_at' => time(), 'created_at' => time()],
        ['queue' => 'medium', 'payload' => json_encode(['displayName' => 'Job']), 'attempts' => 0, 'reserved_at' => null, 'available_at' => time(), 'created_at' => time()],
        ['queue' => 'low', 'payload' => json_encode(['displayName' => 'Job']), 'attempts' => 0, 'reserved_at' => null, 'available_at' => time(), 'created_at' => time()],
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertSuccessful();
    $stats = $response->viewData('stats');

    expect($stats)->toHaveKey('queuedBreakdown');
    $breakdown = $stats['queuedBreakdown'];

    expect($breakdown)->toBeArray();
    expect($breakdown)->toHaveCount(3);
    expect(array_keys($breakdown))->toBe(['high', 'medium', 'low']);
    expect($breakdown['high'])->toBe(3);
    expect($breakdown['medium'])->toBe(2);
    expect($breakdown['low'])->toBe(1);
});
