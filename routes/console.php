<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

// Comm-Link schedules
Schedule::command('comm-link:schedule')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

Schedule::command('comm-link:download-new-versions', ['--skip' => false])
    ->yearly()
    ->withoutOverlapping();

// Stats
Schedule::command('stats:sync')
    ->dailyAt('20:00');

// Vehicles/Ship Matrix
Schedule::command('vehicles:import-ship-matrix')
    ->daily()
    ->then(fn () => Artisan::call('vehicles:import-msrp'))
    ->then(fn () => Artisan::call('sitemap:generate --only=vehicles'));

Schedule::command('pledge-store:import')
    ->daily()
    ->withoutOverlapping();

Schedule::command('vehicles:import-loaner')
    ->daily();

Schedule::command('game:import-images')
    ->monthly();

// Item Prices
Schedule::command('game:import-item-prices')
    ->daily()
    ->then(fn () => Artisan::call('sitemap:generate --only=items,blueprints,commodities,missions,locations'));

// Starmap
Schedule::command('starmap:sync')
    ->monthly()
    ->then(fn () => Artisan::call('sitemap:generate --only=starsystems,celestial-objects'));

// Galactapedia
Schedule::command('galactapedia:sync')
    ->dailyAt('2:00')
    ->withoutOverlapping();

Schedule::command('galactapedia:translate')
    ->dailyAt('3:00')
    ->withoutOverlapping();

// Sitemap: Comm-Links + Galactapedia daily
Schedule::command('sitemap:generate --only=comm-links,galactapedia')
    ->dailyAt('4:00')
    ->withoutOverlapping();
