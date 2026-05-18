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
        if (DB::connection()->getDriverName() === 'pgsql') {
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
        } elseif (DB::connection()->getDriverName() === 'sqlite') {
            $rows = DB::table('game_mission_data_blueprint')
                ->whereNotNull('pool_uuid')
                ->select('mission_data_id', 'pool_uuid')
                ->distinct()
                ->get()
                ->groupBy('mission_data_id');

            foreach ($rows as $missionDataId => $pools) {
                $sorted = $pools->map(fn ($r) => $r->pool_uuid)->sort()->values()->all();
                DB::table('game_mission_data')
                    ->where('id', $missionDataId)
                    ->update(['mission_key' => md5(implode(',', $sorted))]);
            }
        } else {
            DB::statement(<<<'SQL'
                UPDATE game_mission_data
                SET mission_key = (
                    SELECT md5(GROUP_CONCAT(DISTINCT pool_uuid ORDER BY pool_uuid SEPARATOR ','))
                    FROM game_mission_data_blueprint
                    WHERE mission_data_id = game_mission_data.id
                    AND pool_uuid IS NOT NULL
                    GROUP BY mission_data_id
                )
            SQL);
        }

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

        Schema::table('game_mission_data_blueprint', static function (Blueprint $table): void {
            $table->dropPrimary(['mission_data_id', 'blueprint_data_id', 'item_data_id', 'pool_uuid']);
        });

        // Deduplicate rows that differ only by pool_uuid
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement(<<<'SQL'
                DELETE FROM game_mission_data_blueprint
                WHERE ctid NOT IN (
                    SELECT MIN(ctid)
                    FROM game_mission_data_blueprint
                    GROUP BY mission_data_id, blueprint_data_id, item_data_id
                )
            SQL);
        } else {
            DB::statement(<<<'SQL'
                DELETE FROM game_mission_data_blueprint
                WHERE rowid NOT IN (
                    SELECT MIN(rowid)
                    FROM game_mission_data_blueprint
                    GROUP BY mission_data_id, blueprint_data_id, item_data_id
                )
            SQL);
        }

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
