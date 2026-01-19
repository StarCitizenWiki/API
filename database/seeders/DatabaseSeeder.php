<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Game\Manufacturer;
use Database\Seeders\Rsi\CommLink\CategoryTableSeeder;
use Database\Seeders\Rsi\CommLink\ChannelTableSeeder;
use Database\Seeders\Rsi\CommLink\SeriesTableSeeder;
use Database\Seeders\StarCitizen\ProductionNoteTableSeeder;
use Database\Seeders\StarCitizen\ProductionStatusTableSeeder;
use Database\Seeders\StarCitizen\StatTableSeeder;
use Database\Seeders\StarCitizen\Vehicle\SizeTableSeeder;
use Database\Seeders\StarCitizen\Vehicle\TypeTableSeeder;
use Database\Seeders\System\LanguageTableSeeder;
use Illuminate\Database\Seeder;

/**
 * Class DatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->seedSystemTables();
        $this->seedApiTables();
        $this->seedRsiTables();

        Manufacturer::query()->updateOrCreate([
            'uuid' => '00000000-0000-0000-0000-000000000000',
        ], [
            'name' => 'Unknown Manufacturer',
            'code' => 'UNKN',
        ]);
    }

    /**
     * System Tables
     */
    private function seedSystemTables()
    {
        $this->call(LanguageTableSeeder::class);
    }

    /**
     * API Tables
     */
    private function seedApiTables()
    {
        /** Stats */
        // $this->call(StatTableSeeder::class);

        /** Star Citizen General */
        $this->call(ProductionStatusTableSeeder::class);
        $this->call(ProductionNoteTableSeeder::class);

        /** Vehicles */
        $this->call(SizeTableSeeder::class);
        $this->call(TypeTableSeeder::class);
    }

    /**
     * Comm Links
     */
    private function seedRsiTables()
    {
        $this->call(CategoryTableSeeder::class);
        $this->call(ChannelTableSeeder::class);
        $this->call(SeriesTableSeeder::class);
    }
}
