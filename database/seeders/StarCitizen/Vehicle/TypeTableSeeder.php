<?php

declare(strict_types=1);

namespace Database\Seeders\StarCitizen\Vehicle;

use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (Type::query()->count() > 0) {
            return;
        }

        DB::table('shipmatrix_vehicle_types')->insert(
            [
                'id' => 1,
                'slug' => 'undefined',
                'translations' => json_encode([
                    'en' => 'Undefined',
                    'de' => 'Undefiniert',
                ], JSON_THROW_ON_ERROR),
            ]
        );
    }
}
