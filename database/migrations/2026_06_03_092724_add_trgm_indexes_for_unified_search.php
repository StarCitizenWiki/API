<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Add pg_trgm GIN indexes for ILIKE queries
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm');

        // Items
        DB::statement('CREATE INDEX game_item_data_name_trgm_idx ON game_item_data USING gin (name gin_trgm_ops)');
        DB::statement('CREATE INDEX game_item_data_class_name_trgm_idx ON game_item_data USING gin (class_name gin_trgm_ops)');
        DB::statement('CREATE INDEX game_item_data_type_trgm_idx ON game_item_data USING gin (type gin_trgm_ops)');

        // Missions
        DB::statement('CREATE INDEX game_mission_data_title_trgm_idx ON game_mission_data USING gin (title gin_trgm_ops)');
        DB::statement('CREATE INDEX game_mission_data_debug_name_trgm_idx ON game_mission_data USING gin (debug_name gin_trgm_ops)');
        DB::statement('CREATE INDEX game_mission_data_description_trgm_idx ON game_mission_data USING gin (description gin_trgm_ops)');

        // Starmap
        DB::statement('CREATE INDEX game_starmap_location_data_name_trgm_idx ON game_starmap_location_data USING gin (name gin_trgm_ops)');

        // Vehicles
        DB::statement('CREATE INDEX game_vehicle_data_name_trgm_idx ON game_vehicle_data USING gin (name gin_trgm_ops)');
        DB::statement('CREATE INDEX game_vehicle_data_class_name_trgm_idx ON game_vehicle_data USING gin (class_name gin_trgm_ops)');

        // Blueprints
        DB::statement('CREATE INDEX game_blueprint_data_output_name_trgm_idx ON game_blueprint_data USING gin (output_name gin_trgm_ops)');
        DB::statement('CREATE INDEX game_blueprint_data_output_class_trgm_idx ON game_blueprint_data USING gin (output_class gin_trgm_ops)');
        DB::statement('CREATE INDEX game_blueprint_data_key_trgm_idx ON game_blueprint_data USING gin (key gin_trgm_ops)');

        // Commodities
        DB::statement('CREATE INDEX game_commodities_name_trgm_idx ON game_commodities USING gin (name gin_trgm_ops)');
        DB::statement('CREATE INDEX game_commodities_key_trgm_idx ON game_commodities USING gin (key gin_trgm_ops)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $indexes = [
            'game_item_data_name_trgm_idx',
            'game_item_data_class_name_trgm_idx',
            'game_item_data_type_trgm_idx',
            'game_mission_data_title_trgm_idx',
            'game_mission_data_debug_name_trgm_idx',
            'game_mission_data_description_trgm_idx',
            'game_starmap_location_data_name_trgm_idx',
            'game_vehicle_data_name_trgm_idx',
            'game_vehicle_data_class_name_trgm_idx',
            'game_blueprint_data_output_name_trgm_idx',
            'game_blueprint_data_output_class_trgm_idx',
            'game_blueprint_data_key_trgm_idx',
            'game_commodities_name_trgm_idx',
            'game_commodities_key_trgm_idx',
        ];

        foreach ($indexes as $index) {
            DB::statement("DROP INDEX IF EXISTS {$index}");
        }
    }
};
