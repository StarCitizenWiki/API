<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_item_data', static function ($table) {
            $table->index(['game_version_id', 'is_player_relevant', 'name'], 'game_item_data_version_relevant_name_idx');
        });

        Schema::table('game_mission_data_starmap_location', static function ($table) {
            $table->index('starmap_location_data_id', 'game_mission_data_starmap_location_starmap_location_data_id_idx');
        });

        Schema::table('game_resource_location_placements', static function ($table) {
            $table->index('starmap_location_data_id', 'game_resource_location_placements_sld_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_resource_location_placements', function ($table) {
            $table->dropIndex('game_resource_location_placements_sld_id_idx');
        });

        Schema::table('game_mission_data_starmap_location', function ($table) {
            $table->dropIndex('game_mission_data_starmap_location_starmap_location_data_id_idx');
        });

        Schema::table('game_item_data', function ($table) {
            $table->dropIndex('game_item_data_version_relevant_name_idx');
        });
    }
};
