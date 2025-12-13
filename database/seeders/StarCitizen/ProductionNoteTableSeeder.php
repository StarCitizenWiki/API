<?php

declare(strict_types=1);

namespace Database\Seeders\StarCitizen;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductionNoteTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (DB::table('production_notes')->where('id', 1)->exists()) {
            return;
        }

        $now = Carbon::now();

        DB::table('production_notes')->insert(
            [
                'id' => 1,
                'content_hash' => md5('None'),
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
