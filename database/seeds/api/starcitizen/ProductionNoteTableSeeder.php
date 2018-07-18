<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

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
                'language_id' => 1,
                'production_note_id' => 1,
                'translation' => 'None',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        );
    }
}
