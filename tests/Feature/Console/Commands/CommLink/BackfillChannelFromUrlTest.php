<?php

declare(strict_types=1);

use App\Models\Rsi\CommLink\Channel;
use App\Models\Rsi\CommLink\CommLink;

it('backfills undefined channels from URL slug', function () {
    $undefined = Channel::firstOrCreate(['name' => 'Undefined'], ['slug' => 'undefined']);
    $transmission = Channel::firstOrCreate(['name' => 'Transmission'], ['slug' => 'transmission']);

    CommLink::factory()->create([
        'cig_id' => 20001,
        'channel_id' => $transmission->id,
        'url' => 'https://robertsspaceindustries.com/en/comm-link/transmission/20001-Test',
    ]);

    CommLink::factory()->create([
        'cig_id' => 20002,
        'channel_id' => $undefined->id,
        'url' => 'https://robertsspaceindustries.com/en/comm-link/transmission/20002-Test',
    ]);

    CommLink::factory()->create([
        'cig_id' => 20003,
        'channel_id' => $undefined->id,
        'url' => 'https://robertsspaceindustries.com/en/comm-link/transmission/20003-Test',
    ]);

    $this->artisan('comm-link:backfill-channel')
        ->assertSuccessful();

    expect(CommLink::where('cig_id', 20001)->first()->channel_id)->toBe($transmission->id)
        ->and(CommLink::where('cig_id', 20002)->first()->channel_id)->toBe($transmission->id)
        ->and(CommLink::where('cig_id', 20003)->first()->channel_id)->toBe($transmission->id);
});

it('skips records without a comm-link URL', function () {
    $undefined = Channel::firstOrCreate(['name' => 'Undefined'], ['slug' => 'undefined']);

    CommLink::factory()->create([
        'cig_id' => 20010,
        'channel_id' => $undefined->id,
        'url' => null,
    ]);

    CommLink::factory()->create([
        'cig_id' => 20011,
        'channel_id' => $undefined->id,
        'url' => 'https://robertsspaceindustries.com/Service-Alert',
    ]);

    $this->artisan('comm-link:backfill-channel')
        ->assertSuccessful();

    expect(CommLink::where('cig_id', 20010)->first()->channel_id)->toBe($undefined->id)
        ->and(CommLink::where('cig_id', 20011)->first()->channel_id)->toBe($undefined->id);
});

it('skips URL slugs not in channels table', function () {
    $undefined = Channel::firstOrCreate(['name' => 'Undefined'], ['slug' => 'undefined']);

    CommLink::factory()->create([
        'cig_id' => 20020,
        'channel_id' => $undefined->id,
        'url' => 'https://robertsspaceindustries.com/en/comm-link/unknown-channel/20020-Test',
    ]);

    $this->artisan('comm-link:backfill-channel')
        ->assertSuccessful();

    expect(CommLink::where('cig_id', 20020)->first()->channel_id)->toBe($undefined->id);
});

it('does not change anything in dry-run mode', function () {
    $undefined = Channel::firstOrCreate(['name' => 'Undefined'], ['slug' => 'undefined']);
    Channel::firstOrCreate(['name' => 'Transmission'], ['slug' => 'transmission']);

    CommLink::factory()->create([
        'cig_id' => 20030,
        'channel_id' => $undefined->id,
        'url' => 'https://robertsspaceindustries.com/en/comm-link/transmission/20030-Test',
    ]);

    $this->artisan('comm-link:backfill-channel --dry-run')
        ->assertSuccessful();

    expect(CommLink::where('cig_id', 20030)->first()->channel_id)->toBe($undefined->id);
});

it('fixes mismatched channels when --fix-mismatches is used', function () {
    $transmission = Channel::firstOrCreate(['name' => 'Transmission'], ['slug' => 'transmission']);
    $engineering = Channel::firstOrCreate(['name' => 'Engineering'], ['slug' => 'engineering']);

    CommLink::factory()->create([
        'cig_id' => 20040,
        'channel_id' => $engineering->id,
        'url' => 'https://robertsspaceindustries.com/en/comm-link/transmission/20040-Test',
    ]);

    // Without --fix-mismatches: no change
    $this->artisan('comm-link:backfill-channel')
        ->assertSuccessful();

    expect(CommLink::where('cig_id', 20040)->first()->channel_id)->toBe($engineering->id);

    // With --fix-mismatches: fixed
    $this->artisan('comm-link:backfill-channel --fix-mismatches')
        ->assertSuccessful();

    expect(CommLink::where('cig_id', 20040)->first()->channel_id)->toBe($transmission->id);
});

it('resolves multiple channel slugs', function () {
    $undefined = Channel::firstOrCreate(['name' => 'Undefined'], ['slug' => 'undefined']);
    $transmission = Channel::firstOrCreate(['name' => 'Transmission'], ['slug' => 'transmission']);
    $engineering = Channel::firstOrCreate(['name' => 'Engineering'], ['slug' => 'engineering']);
    $citizens = Channel::firstOrCreate(['name' => 'Citizens'], ['slug' => 'citizens']);

    CommLink::factory()->create(['cig_id' => 20050, 'channel_id' => $undefined->id, 'url' => 'https://robertsspaceindustries.com/en/comm-link/transmission/20050-A']);
    CommLink::factory()->create(['cig_id' => 20051, 'channel_id' => $undefined->id, 'url' => 'https://robertsspaceindustries.com/en/comm-link/engineering/20051-B']);
    CommLink::factory()->create(['cig_id' => 20052, 'channel_id' => $undefined->id, 'url' => 'https://robertsspaceindustries.com/en/comm-link/citizens/20052-C']);

    $this->artisan('comm-link:backfill-channel')
        ->assertSuccessful();

    expect(CommLink::where('cig_id', 20050)->first()->channel_id)->toBe($transmission->id)
        ->and(CommLink::where('cig_id', 20051)->first()->channel_id)->toBe($engineering->id)
        ->and(CommLink::where('cig_id', 20052)->first()->channel_id)->toBe($citizens->id);
});
