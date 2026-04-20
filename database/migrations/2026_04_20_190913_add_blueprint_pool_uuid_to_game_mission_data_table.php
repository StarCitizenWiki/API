<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_mission_data', static function (Blueprint $table) {
            $table->uuid('blueprint_pool_uuid')->nullable()->after('blueprint_drop_chance');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                UPDATE game_mission_data md
                SET blueprint_pool_uuid = sub.pool::uuid
                FROM (
                    SELECT mission_data_id, min(pool_uuid::text) AS pool
                    FROM game_mission_data_blueprint
                    GROUP BY mission_data_id
                ) sub
                WHERE md.id = sub.mission_data_id
            SQL);
        } else {
            DB::statement(<<<'SQL'
                UPDATE game_mission_data
                SET blueprint_pool_uuid = (
                    SELECT min(pool_uuid)
                    FROM game_mission_data_blueprint
                    WHERE mission_data_id = game_mission_data.id
                )
            SQL);
        }
    }

    public function down(): void
    {
        Schema::table('game_mission_data', function (Blueprint $table) {
            $table->dropColumn('blueprint_pool_uuid');
        });
    }
};
