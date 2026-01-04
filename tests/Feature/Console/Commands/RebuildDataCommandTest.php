<?php

declare(strict_types=1);

use App\Console\Commands\RebuildData;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

it('runs the full rebuild flow', function (): void {
    Artisan::shouldReceive('call')->once()->with('db:wipe', [])->andReturn(0)->ordered();
    Artisan::shouldReceive('call')->once()->with('migrate', [])->andReturn(0)->ordered();
    Artisan::shouldReceive('call')->once()->with('game:add-version', ['code' => '4.5.1', '--default' => true])->andReturn(0)->ordered();
    Artisan::shouldReceive('call')->once()->with('data:migrate', ['--all' => true])->andReturn(0)->ordered();
    Artisan::shouldReceive('call')->once()->with('game:sync', [])->andReturn(0)->ordered();
    Artisan::shouldReceive('call')->once()->with('starmap:sync', [])->andReturn(0)->ordered();
    Artisan::shouldReceive('call')->once()->with('data:migrate-translations', [])->andReturn(0)->ordered();

    $command = app(RebuildData::class);
    $command->setLaravel(app());
    $exitCode = $command->run(new ArrayInput(['version' => '4.5.1']), new BufferedOutput);

    expect($exitCode)->toBe(0);
});

it('stops when a step fails and forwards --force', function (): void {
    Artisan::shouldReceive('call')->once()->with('db:wipe', ['--force' => true])->andReturn(0)->ordered();
    Artisan::shouldReceive('call')->once()->with('migrate', ['--force' => true])->andReturn(1)->ordered();
    Artisan::shouldReceive('call')->with('game:add-version', [])->never();

    $command = app(RebuildData::class);
    $command->setLaravel(app());
    $exitCode = $command->run(new ArrayInput(['version' => '4.5.1', '--force' => true]), new BufferedOutput);

    expect($exitCode)->toBe(1);
});
