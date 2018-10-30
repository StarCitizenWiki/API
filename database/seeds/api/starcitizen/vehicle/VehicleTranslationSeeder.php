<?php declare(strict_types = 1);

use Illuminate\Database\Seeder;

class VehicleTranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::unprepared(file_get_contents(database_path('dumps/vehicle_translations/focus.sql')));
        DB::unprepared(file_get_contents(database_path('dumps/vehicle_translations/size.sql')));
        DB::unprepared(file_get_contents(database_path('dumps/vehicle_translations/type.sql')));

        DB::unprepared(file_get_contents(database_path('dumps/vehicle_translations/production_note.sql')));
        DB::unprepared(file_get_contents(database_path('dumps/vehicle_translations/production_status.sql')));
    }
}
