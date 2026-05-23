<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

it('displays dashboard stats and admin links', function (): void {
    User::factory()->create();
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
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'Default Job']),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ],
        [
            'queue' => 'expensive',
            'payload' => json_encode(['displayName' => 'Expensive Job']),
            'attempts' => 0,
            'reserved_at' => null,
            'available_at' => time(),
            'created_at' => time(),
        ],
    ]);

    DB::table('failed_jobs')->insert([
        [
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'default',
            'payload' => json_encode(['displayName' => 'Default Job']),
            'exception' => 'Test exception 1',
            'failed_at' => now(),
        ],
        [
            'uuid' => (string) Str::uuid(),
            'connection' => 'database',
            'queue' => 'critical',
            'payload' => json_encode(['displayName' => 'Critical Job']),
            'exception' => 'Test exception 2',
            'failed_at' => now(),
        ],
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.dashboard'));

    $response->assertSuccessful()
        ->assertSeeText('Admin Dashboard')
        ->assertSeeText('Total Users')
        ->assertSeeText('Total Queued Jobs')
        ->assertSeeText('Total Failed Jobs')
        ->assertSeeText('Manage Users')
        ->assertSeeText('Game Versions')
        ->assertSeeText('Translations')
        ->assertSeeText('View Details')
        ->assertSeeText('2')
        ->assertSeeText('3')
        ->assertSeeText('default: 2')
        ->assertSeeText('expensive: 1')
        ->assertSee(route('admin.users.index'), false)
        ->assertSee(route('admin.jobs.index'), false)
        ->assertSee(route('admin.game-versions.index'), false)
        ->assertSee(route('admin.translations.index'), false);
});
