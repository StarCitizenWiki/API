<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Schedule::command('comm-link:schedule')
    ->everyFifteenMinutes()
    ->withoutOverlapping();

Schedule::command('comm-link:download-new-versions', ['--skip' => false])
    ->monthly()
    ->withoutOverlapping();
