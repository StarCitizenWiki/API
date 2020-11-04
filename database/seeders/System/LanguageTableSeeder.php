<?php

declare(strict_types=1);

namespace Database\Seeders\System;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = \Carbon\Carbon::now();

        DB::table('languages')->insert(
            [
                'locale_code' => 'en_EN',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
        DB::table('languages')->insert(
            [
                'locale_code' => 'de_DE',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
