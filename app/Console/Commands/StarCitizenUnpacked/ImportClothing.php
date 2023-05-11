<?php

declare(strict_types=1);

namespace App\Console\Commands\StarCitizenUnpacked;

use App\Console\Commands\AbstractQueueCommand;

class ImportClothing extends AbstractQueueCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unpacked:import-clothing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Clothes and Armor';

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
                '--type' => 'Char_Clothing_Torso_1,Char_Clothing_Legs,Char_Clothing_Torso_0,Char_Clothing_Feet,Char_Clothing_Hat,Char_Armor_Backpack,Char_Clothing_Hands,Char_Armor_Helmet,Char_Armor_Arms,Char_Armor_Torso,Char_Armor_Legs,Char_Armor_Undersuit,Char_Clothing_Torso_2,Char_Clothing_Backpack'
            ]
        );
    }
}
