<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_mission_data', static function (Blueprint $table): void {
            $table->string('mission_key', 32)->nullable()->after('reward_scope');
            $table->index('mission_key');
        });

        // Backfill mission_key from existing pivot data: Key = MD5 of sorted UUIDs
        DB::statement(<<<'SQL'
            UPDATE game_mission_data md
            SET mission_key = sub.key
            FROM (
                SELECT
                    mission_data_id,
                    md5(string_agg(DISTINCT pool_uuid::text, ',' ORDER BY pool_uuid::text)) AS key
                FROM game_mission_data_blueprint
                WHERE pool_uuid IS NOT NULL
                GROUP BY mission_data_id
            ) sub
            WHERE md.id = sub.mission_data_id
        SQL);

        Schema::table('game_mission_data_blueprint', static function (Blueprint $table): void {
            $table->dropPrimary(['mission_data_id', 'blueprint_data_id', 'item_data_id']);
        });

        Schema::table('game_mission_data_blueprint', static function (Blueprint $table): void {
            $table->uuid('pool_uuid')->nullable()->change();
            $table->float('chance')->nullable();
            $table->primary(['mission_data_id', 'blueprint_data_id', 'item_data_id', 'pool_uuid']);
        });

        Schema::table('game_mission_data', static function (Blueprint $table): void {
            $table->dropColumn('blueprint_pool_uuid');
            $table->dropColumn('blueprint_drop_chance');
        });
    }

    public function down(): void
    {
        Schema::table('game_mission_data', static function (Blueprint $table): void {
            $table->uuid('blueprint_pool_uuid')->nullable()->after('reward_scope');
            $table->float('blueprint_drop_chance')->nullable()->after('blueprint_pool_uuid');
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

        Schema::table('game_mission_data_blueprint', static function (Blueprint $table): void {
            $table->dropPrimary(['mission_data_id', 'blueprint_data_id', 'item_data_id', 'pool_uuid']);
        });

        // Deduplicate rows that differ only by pool_uuid
        DB::statement(<<<'SQL'
            DELETE FROM game_mission_data_blueprint
            WHERE ctid NOT IN (
                SELECT MIN(ctid)
                FROM game_mission_data_blueprint
                GROUP BY mission_data_id, blueprint_data_id, item_data_id
            )
        SQL);

        Schema::table('game_mission_data_blueprint', static function (Blueprint $table): void {
            $table->primary(['mission_data_id', 'blueprint_data_id', 'item_data_id']);
        });

        Schema::table('game_mission_data_blueprint', static function (Blueprint $table): void {
            $table->dropColumn('chance');
        });

        Schema::table('game_mission_data', static function (Blueprint $table): void {
            $table->dropColumn('mission_key');
        });
    }
};
