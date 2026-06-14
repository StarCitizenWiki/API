<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_mission_data', function (Blueprint $table): void {
            $table->integer('max_players_per_instance')->nullable();
            $table->integer('reputation_amount')->nullable();
        });

        $this->backfillPostgres();

        Schema::table('game_blueprint_data', function (Blueprint $table): void {
            $table->integer('unlocking_missions_count')->default(0);
        });

        $this->backfillBlueprintCountPostgres();
    }

    public function down(): void
    {
        Schema::table('game_blueprint_data', function (Blueprint $table): void {
            $table->dropColumn('unlocking_missions_count');
        });

        Schema::table('game_mission_data', function (Blueprint $table): void {
            $table->dropColumn([
                'reputation_amount',
                'max_players_per_instance',
            ]);
        });
    }

    private function backfillPostgres(): void
    {
        DB::statement("
            UPDATE game_mission_data
            SET max_players_per_instance = (data #>> '{MaxPlayersPerInstance}')::integer
            WHERE (data #>> '{MaxPlayersPerInstance}') ~ '^\\d+$'
        ");

        DB::statement("
            UPDATE game_mission_data
            SET reputation_amount = (data -> 'ReputationGained' -> 0 ->> 'Amount')::integer
            WHERE data -> 'ReputationGained' IS NOT NULL
              AND jsonb_array_length(data -> 'ReputationGained') > 0
        ");
    }

    private function backfillBlueprintCountPostgres(): void
    {
        DB::statement('
            UPDATE game_blueprint_data bd
            SET unlocking_missions_count = aggregated.cnt
            FROM (
                SELECT blueprint_data_id, COUNT(*) AS cnt
                FROM game_mission_data_blueprint mdb
                INNER JOIN game_mission_data md ON mdb.mission_data_id = md.id
                GROUP BY blueprint_data_id
            ) aggregated
            WHERE bd.id = aggregated.blueprint_data_id
        ');
    }
};
