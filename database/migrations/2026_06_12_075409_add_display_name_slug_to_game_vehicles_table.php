<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_vehicles', function (Blueprint $table) {
            $table->string('display_name_slug')->nullable()->after('slug');
        });

        DB::table('game_vehicle_data')
            ->selectRaw('vehicle_id, display_name, MAX(game_version_id) as max_version')
            ->whereNotNull('display_name')
            ->where('display_name', '!=', '')
            ->groupBy('vehicle_id', 'display_name')
            ->orderBy('vehicle_id')
            ->chunk(200, function ($rows): void {
                $updates = [];
                foreach ($rows as $row) {
                    $slug = Str::slug($row->display_name);

                    if ($slug !== '') {
                        $updates[$row->vehicle_id] = $slug;
                    }
                }

                foreach ($updates as $vehicleId => $slug) {
                    DB::table('game_vehicles')
                        ->where('id', $vehicleId)
                        ->update(['display_name_slug' => $slug]);
                }
            });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('
                CREATE INDEX IF NOT EXISTS game_vehicle_data_display_name_trgm_idx
                ON game_vehicle_data USING gin (display_name gin_trgm_ops)
            ');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_vehicles', function (Blueprint $table) {
            $table->dropColumn('display_name_slug');
        });

        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS game_vehicle_data_display_name_trgm_idx');
        }
    }
};
