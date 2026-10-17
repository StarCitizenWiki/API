<?php

use App\Jobs\StarCitizen\Galactapedia\Sync\SyncGalactapedia;
use App\Jobs\StarCitizen\Starmap\Sync\SyncStarmap;
use App\Jobs\StarCitizen\Stat\SyncStats;
use App\Jobs\StarCitizen\Vehicle\ImportLoaner;
use App\Jobs\StarCitizen\Vehicle\ImportMsrp;
use App\Jobs\StarCitizen\Vehicle\ImportPledgeStore;
use App\Jobs\StarCitizen\Vehicle\ImportShipMatrix;
use App\Jobs\StarCitizen\Vehicle\SyncVehicleCuratedData;
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
Schedule::job(new SyncStats)
    ->dailyAt('20:00');

// Vehicles/Ship Matrix
Schedule::job(new ImportShipMatrix)
    ->daily()
    ->then(fn () => ImportMsrp::dispatch())
    ->then(fn () => Artisan::call('sitemap:generate --only=vehicles'));

Schedule::job(new ImportPledgeStore)
    ->daily()
    ->withoutOverlapping();

// Vehicle curated data (starcitizen.tools wiki infobox sync)
Schedule::job(new SyncVehicleCuratedData)
    ->daily()
    ->withoutOverlapping();

Schedule::job(new ImportLoaner)
    ->daily();

Schedule::command('game:import-images')
    ->monthly();

// Item Prices
Schedule::command('game:import-item-prices')
    ->daily()
    ->then(fn () => Artisan::call('sitemap:generate --only=items,blueprints,commodities,missions,locations'));

// Starmap
Schedule::job(new SyncStarmap)
    ->monthly()
    ->then(fn () => Artisan::call('sitemap:generate --only=starsystems,celestial-objects'));

// Galactapedia
Schedule::job(new SyncGalactapedia)
    ->dailyAt('2:00')
    ->withoutOverlapping();

Schedule::command('galactapedia:translate')
    ->dailyAt('3:00')
    ->withoutOverlapping();

// Sitemap: Comm-Links + Galactapedia daily
Schedule::command('sitemap:generate --only=comm-links,galactapedia')
    ->dailyAt('4:00')
    ->withoutOverlapping();

// Queue: prune old job batches (sync + price imports create them continuously).
Schedule::command('queue:prune-batches --hours=48 --unfinished=72 --cancelled=72')->daily();
