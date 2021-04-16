<?php

namespace App\Console\Commands\StarCitizenUnpacked;

use App\Jobs\StarCitizenUnpacked\Import\WeaponPersonal;
use Illuminate\Console\Command;

class ImportWeaponPersonal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:import-weapon-personal';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import personal weapons from scunpacked';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        WeaponPersonal::dispatch();
        return 0;
    }
}
