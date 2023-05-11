<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked;

use App\Console\Commands\AbstractQueueCommand;

class ImportPersonalWeapons extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:import-personal-weapons';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Personal Weapons';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        return $this->call(
    'unpacked:import-items',
            [
                '--skipVehicles',
                '--type' => 'WeaponPersonal'
            ]
        );
    }
}
