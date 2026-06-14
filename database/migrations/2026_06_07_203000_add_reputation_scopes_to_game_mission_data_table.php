<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_mission_data', static function (Blueprint $table): void {
            $table->jsonb('reputation_scopes')->nullable()->after('reward_scope');
        });

        DB::statement(<<<'SQL'
            UPDATE game_mission_data AS gmd
            SET reputation_scopes = COALESCE((
                SELECT jsonb_agg(scope)
                FROM (
                    SELECT DISTINCT elem->>'Scope' AS scope
                    FROM jsonb_array_elements(
                        CASE
                            WHEN jsonb_typeof(gmd.data->'ReputationGained') = 'array'
                                THEN gmd.data->'ReputationGained'
                            ELSE '[]'::jsonb
                        END
                    ) AS elem
                    WHERE elem->>'Scope' IS NOT NULL
                        AND btrim(elem->>'Scope') <> ''
                    ORDER BY scope
                ) AS scopes
            ), '[]'::jsonb)
            WHERE gmd.reputation_scopes IS NULL
                AND gmd.data->'ReputationGained' IS NOT NULL
        SQL);

        DB::statement(<<<'SQL'
            CREATE INDEX game_mission_data_reputation_scopes_gin_index
            ON game_mission_data
            USING GIN (reputation_scopes)
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX game_mission_data_reputation_scopes_gin_index');

        Schema::table('game_mission_data', static function (Blueprint $table): void {
            $table->dropColumn('reputation_scopes');
        });
    }
};
