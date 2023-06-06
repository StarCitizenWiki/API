<?php

declare(strict_types=1);

namespace Database\Seeders\StarCitizen\Vehicle;

use App\Models\StarCitizen\Vehicle\Size\Size;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SizeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        if (Size::query()->count() > 0) {
            return;
        }

        $now = Carbon::now();

        DB::table('vehicle_sizes')->insert(
            [
                'id' => 1,
                'slug' => 'undefined',
            ]
        );
        DB::table('vehicle_size_translations')->insert(
            [
                'locale_code' => 'en_EN',
                'size_id' => 1,
                'translation' => 'undefined',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('vehicle_size_translations')->insert(
            [
                'locale_code' => 'de_DE',
                'size_id' => 1,
                'translation' => 'Undefiniert',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
