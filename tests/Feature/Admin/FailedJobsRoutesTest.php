<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

/**
 * @param  array{
 *     uuid?: string,
 *     connection?: string,
 *     queue?: string,
 *     payload?: string,
 *     exception?: string,
 *     failed_at?: \DateTimeInterface|string
 * }  $overrides
 */
$insertFailedJob = static function (array $overrides = []): int {
    $payload = json_encode([
        'uuid' => (string) Str::uuid(),
        'displayName' => 'App\\Jobs\\SyncExternalMetrics',
        'job' => 'Illuminate\\Queue\\CallQueuedHandler@call',
        'maxTries' => null,
        'maxExceptions' => null,
        'failOnTimeout' => false,
        'backoff' => null,
        'timeout' => null,
        'retryUntil' => null,
        'data' => [
            'commandName' => 'App\\Jobs\\SyncExternalMetrics',
            'command' => 'O:27:"App\\Jobs\\SyncExternalMetrics":0:{}',
        ],
    ], JSON_THROW_ON_ERROR);

    $attributes = array_merge([
        'uuid' => (string) Str::uuid(),
        'connection' => 'database',
        'queue' => 'default',
        'payload' => $payload,
        'exception' => "RuntimeException: Upstream service unavailable in /var/www/app/Jobs/SyncExternalMetrics.php:42\n#0 /var/www/app/vendor/laravel/framework/src/Illuminate/Container/BoundMethod.php(36): App\\Jobs\\SyncExternalMetrics->handle()",
        'failed_at' => now(),
    ], $overrides);

    return (int) DB::table('failed_jobs')->insertGetId($attributes);
};

it('R-ADMIN-004 redirects guests to login for failed jobs index', function (): void {
    $response = $this->get(route('admin.jobs.index'));

    $response->assertRedirect(route('login'));
});

it('R-ADMIN-004 forbids non-admin users from failed jobs index', function (): void {
    $user = User::factory()->create(['is_admin' => false]);

    $response = $this->actingAs($user)->get(route('admin.jobs.index'));

    $response->assertForbidden();
});

it('R-ADMIN-004 allows admin users to view failed jobs index with paginated jobs data', function () use ($insertFailedJob): void {
    $admin = User::factory()->create(['is_admin' => true]);

    $olderJobId = $insertFailedJob([
        'queue' => 'emails',
        'failed_at' => now()->subMinutes(5),
    ]);

    $newerJobId = $insertFailedJob([
        'queue' => 'critical',
        'exception' => "RuntimeException: Gateway timeout in /var/www/app/Jobs/SyncExternalMetrics.php:53\n#0 /var/www/app/vendor/laravel/framework/src/Illuminate/Queue/Worker.php(423): App\\Jobs\\SyncExternalMetrics->handle()",
        'failed_at' => now()->subMinute(),
    ]);

    $response = $this->actingAs($admin)
        ->get(route('admin.jobs.index'));

    $response->assertSuccessful();
    $response->assertViewIs('admin.jobs.index');
    $response->assertViewHas('jobs', function (LengthAwarePaginator $jobs) use ($olderJobId, $newerJobId): bool {
        $collection = $jobs->getCollection();

        $olderJob = $collection->first(static fn (object $job): bool => (int) $job->id === $olderJobId);
        $newerJob = $collection->first(static fn (object $job): bool => (int) $job->id === $newerJobId);

        return $jobs->total() === 2
            && $jobs->count() === 2
            && $olderJob !== null
            && $newerJob !== null
            && $olderJob->queue === 'emails'
            && $newerJob->queue === 'critical'
            && str_contains((string) $newerJob->exception, 'Gateway timeout');
    });
    $response->assertSeeText((string) $olderJobId);
    $response->assertSeeText((string) $newerJobId);
    $response->assertSeeText('emails');
    $response->assertSeeText('critical');
});

it('R-ADMIN-005 redirects guests to login for deleting a failed job', function () use ($insertFailedJob): void {
    $failedJobId = $insertFailedJob();

    $response = $this->delete(route('admin.jobs.destroy', ['id' => $failedJobId]));

    $response->assertRedirect(route('login'));
});

it('R-ADMIN-005 forbids non-admin users from deleting a failed job', function () use ($insertFailedJob): void {
    $user = User::factory()->create(['is_admin' => false]);
    $failedJobId = $insertFailedJob();

    $response = $this->actingAs($user)
        ->delete(route('admin.jobs.destroy', ['id' => $failedJobId]));

    $response->assertForbidden();
    $this->assertDatabaseHas('failed_jobs', ['id' => $failedJobId]);
});

it('R-ADMIN-005 allows admin users to delete a failed job and redirects with success flash', function () use ($insertFailedJob): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $targetFailedJobId = $insertFailedJob(['queue' => 'critical']);
    $keptFailedJobId = $insertFailedJob(['queue' => 'default']);

    $response = $this->actingAs($admin)
        ->delete(route('admin.jobs.destroy', ['id' => $targetFailedJobId]));

    $response->assertRedirect(route('admin.jobs.index'));
    $response->assertSessionHas('success', 'Failed job deleted successfully.');
    $this->assertDatabaseMissing('failed_jobs', ['id' => $targetFailedJobId]);
    $this->assertDatabaseHas('failed_jobs', ['id' => $keptFailedJobId]);
});

it('R-ADMIN-003 redirects guests to login for truncating failed jobs', function (): void {
    $response = $this->delete(route('admin.jobs.truncate'));

    $response->assertRedirect(route('login'));
});

it('R-ADMIN-003 forbids non-admin users from truncating failed jobs', function () use ($insertFailedJob): void {
    $user = User::factory()->create(['is_admin' => false]);
    $insertFailedJob();

    $response = $this->actingAs($user)
        ->delete(route('admin.jobs.truncate'));

    $response->assertForbidden();
    $this->assertDatabaseCount('failed_jobs', 1);
});

it('R-ADMIN-003 allows admin users to truncate failed jobs and redirects with success flash', function () use ($insertFailedJob): void {
    $admin = User::factory()->create(['is_admin' => true]);
    $insertFailedJob(['queue' => 'critical']);
    $insertFailedJob(['queue' => 'emails']);

    $response = $this->actingAs($admin)
        ->delete(route('admin.jobs.truncate'));

    $response->assertRedirect(route('admin.jobs.index'));
    $response->assertSessionHas('success', 'All failed jobs have been deleted.');
    $this->assertDatabaseCount('failed_jobs', 0);
});
