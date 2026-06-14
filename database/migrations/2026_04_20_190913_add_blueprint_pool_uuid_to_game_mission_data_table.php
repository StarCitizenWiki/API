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
    }

    public function down(): void
    {
        Schema::table('game_mission_data', function (Blueprint $table) {
            $table->dropColumn('blueprint_pool_uuid');
        });
    }
};
