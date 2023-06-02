<?php

declare(strict_types=1);

namespace App\Console\Commands\SC;

use App\Console\Commands\AbstractQueueCommand;

class ImportPersonalWeapons extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sc:import-weapons';

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
    'sc:import-items',
            [
                '--skipVehicles',
                '--type' => 'WeaponPersonal'
            ]
        );
    }
}
