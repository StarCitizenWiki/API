<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Comm-Link schedules
Schedule::command('comm-link:schedule')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

Schedule::command('comm-link:download-new-versions', ['--skip' => false])
    ->monthly()
    ->withoutOverlapping();

// Stats
Schedule::command('stats:sync')
    ->dailyAt('20:00');

// Vehicles/Ship Matrix
Schedule::command('vehicles:import-ship-matrix')
    ->daily();

Schedule::command('vehicles:import-msrp')
    ->daily();

Schedule::command('vehicles:import-loaner')
    ->daily();

// Starmap
Schedule::command('starmap:sync')
    ->monthly();

// Galactapedia
Schedule::command('galactapedia:sync')
    ->dailyAt('2:00')
    ->withoutOverlapping();

Schedule::command('galactapedia:translate')
    ->dailyAt('3:00')
    ->withoutOverlapping();
