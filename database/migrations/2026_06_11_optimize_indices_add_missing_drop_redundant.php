<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_resource_commodity', function (Blueprint $table) {
            $table->index(['commodity_id'], 'game_resource_commodity_commodity_id_index');
        });

        Schema::table('game_mission_data_blueprint', function (Blueprint $table) {
            $table->index(['blueprint_data_id'], 'game_mission_data_blueprint_blueprint_data_id_index');
        });

        Schema::table('game_item_data_commodity', function (Blueprint $table) {
            $table->index(['commodity_id'], 'game_item_data_commodity_commodity_id_index');
        });

        Schema::table('starmap_celestial_objects', function (Blueprint $table) {
            $table->dropIndex('starmap_celestial_objects_cig_id_index');
            $table->dropIndex('starmap_celestial_objects_code_index');
        });

        Schema::table('starmap_starsystems', function (Blueprint $table) {
            $table->dropIndex('starmap_starsystems_cig_id_index');
            $table->dropIndex('starmap_starsystems_status_index');
        });

        Schema::table('game_starmap_location_data', function (Blueprint $table) {
            $table->dropIndex('game_starmap_location_data_game_version_id_index');
        });

        Schema::table('game_blueprint_data', function (Blueprint $table) {
            $table->dropIndex('game_blueprint_data_game_version_id_index');
        });

        Schema::table('game_item_data', function (Blueprint $table) {
            $table->dropIndex('game_item_data_base_id_game_version_id_index');
        });

        Schema::table('game_mission_data', function (Blueprint $table) {
            $table->dropIndex('game_mission_data_game_version_id_index');
        });
    }

    public function down(): void
    {
        Schema::table('game_mission_data', function (Blueprint $table) {
            $table->index(['game_version_id'], 'game_mission_data_game_version_id_index');
        });

        Schema::table('game_item_data', function (Blueprint $table) {
            $table->index(['base_id', 'game_version_id'], 'game_item_data_base_id_game_version_id_index');
        });

        Schema::table('game_blueprint_data', function (Blueprint $table) {
            $table->index(['game_version_id'], 'game_blueprint_data_game_version_id_index');
        });

        Schema::table('game_starmap_location_data', function (Blueprint $table) {
            $table->index(['game_version_id'], 'game_starmap_location_data_game_version_id_index');
        });

        Schema::table('starmap_starsystems', function (Blueprint $table) {
            $table->index(['status'], 'starmap_starsystems_status_index');
            $table->index(['cig_id'], 'starmap_starsystems_cig_id_index');
        });

        Schema::table('starmap_celestial_objects', function (Blueprint $table) {
            $table->index(['code'], 'starmap_celestial_objects_code_index');
            $table->index(['cig_id'], 'starmap_celestial_objects_cig_id_index');
        });

        Schema::table('game_item_data_commodity', function (Blueprint $table) {
            $table->dropIndex('game_item_data_commodity_commodity_id_index');
        });

        Schema::table('game_mission_data_blueprint', function (Blueprint $table) {
            $table->dropIndex('game_mission_data_blueprint_blueprint_data_id_index');
        });

        Schema::table('game_resource_commodity', function (Blueprint $table) {
            $table->dropIndex('game_resource_commodity_commodity_id_index');
        });
    }
};
