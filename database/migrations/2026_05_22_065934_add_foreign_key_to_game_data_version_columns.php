<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cascade Delete game versioned data
     */
    public function up(): void
    {
        $tables = [
            'game_item_data' => 'game_item_data_version_fk',
            'game_vehicle_data' => 'game_vehicle_data_version_fk',
            'game_blueprint_data' => 'game_blueprint_data_version_fk',
            'game_mission_data' => 'game_mission_data_version_fk',
        ];

        foreach ($tables as $table => $fkName) {
            // Remove orphaned rows before adding the constraint
            DB::table($table)
                ->whereNotNull('game_version_id')
                ->whereNotIn('game_version_id', DB::table('game_versions')->pluck('id'))
                ->delete();

            Schema::table($table, function (Blueprint $t) use ($fkName): void {
                $t->foreign('game_version_id', $fkName)
                    ->references('id')
                    ->on('game_versions')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'game_item_data' => 'game_item_data_version_fk',
            'game_vehicle_data' => 'game_vehicle_data_version_fk',
            'game_blueprint_data' => 'game_blueprint_data_version_fk',
            'game_mission_data' => 'game_mission_data_version_fk',
        ];

        foreach ($tables as $table => $fkName) {
            Schema::table($table, function (Blueprint $t) use ($fkName): void {
                $t->dropForeign($fkName);
            });
        }
    }
};
