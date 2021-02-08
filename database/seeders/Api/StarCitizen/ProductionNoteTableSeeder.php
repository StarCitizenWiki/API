<?php

declare(strict_types=1);

namespace Database\Seeders\Api\StarCitizen;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionNoteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = \Carbon\Carbon::now();

        DB::table('production_notes')->insert(
            [
                'id' => 1,
            ]
        );
        DB::table('production_note_translations')->insert(
            [
                'locale_code' => 'en_EN',
                'production_note_id' => 1,
                'translation' => 'None',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('production_note_translations')->insert(
            [
                'locale_code' => 'de_DE',
                'production_note_id' => 1,
                'translation' => 'Keine',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
