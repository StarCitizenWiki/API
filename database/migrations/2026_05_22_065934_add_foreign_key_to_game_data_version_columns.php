<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Cascade Delete game versioned data
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $tables = [
            'game_item_data' => 'game_item_data_version_fk',
            'game_vehicle_data' => 'game_vehicle_data_version_fk',
            'game_blueprint_data' => 'game_blueprint_data_version_fk',
            'game_mission_data' => 'game_mission_data_version_fk',
        ];

        foreach ($tables as $table => $fkName) {
            DB::statement(
                "DELETE FROM {$table} WHERE game_version_id IS NOT NULL AND game_version_id NOT IN (SELECT id FROM game_versions)"
            );

            DB::statement(
                "ALTER TABLE {$table} ADD CONSTRAINT {$fkName} FOREIGN KEY (game_version_id) REFERENCES game_versions(id) ON DELETE CASCADE"
            );
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return;
        }

        $tables = [
            'game_item_data' => 'game_item_data_version_fk',
            'game_vehicle_data' => 'game_vehicle_data_version_fk',
            'game_blueprint_data' => 'game_blueprint_data_version_fk',
            'game_mission_data' => 'game_mission_data_version_fk',
        ];

        foreach ($tables as $table => $fkName) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$fkName}");
        }
    }
};
