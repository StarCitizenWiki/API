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
        Schema::table('game_starmap_location_data', static function (Blueprint $table): void {
            $table->unsignedInteger('mission_count')->default(0)->after('block_travel');
        });

        // Backfill
        DB::update(<<<'SQL'
            UPDATE game_starmap_location_data gsld
            SET mission_count = aggregated.cnt
            FROM (
                SELECT starmap_location_data_id, COUNT(*) as cnt
                FROM game_mission_data_starmap_location
                GROUP BY starmap_location_data_id
            ) aggregated
            WHERE gsld.id = aggregated.starmap_location_data_id
        SQL);
    }

    public function down(): void
    {
        Schema::table('game_starmap_location_data', static function (Blueprint $table): void {
            $table->dropColumn('mission_count');
        });
    }
};
