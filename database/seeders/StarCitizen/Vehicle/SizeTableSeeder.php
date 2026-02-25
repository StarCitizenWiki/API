<?php

declare(strict_types=1);

namespace Database\Seeders\StarCitizen\Vehicle;

use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Size::query()->count() > 0) {
            return;
        }

        DB::table('shipmatrix_vehicle_sizes')->insert(
            [
                'id' => 1,
                'slug' => 'undefined',
                'translation' => json_encode([
                    'en' => 'Undefined',
                    'de' => 'Undefiniert',
                ], JSON_THROW_ON_ERROR),
            ]
        );
    }
}
