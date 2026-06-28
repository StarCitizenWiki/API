<?php

use App\Models\Game\ItemData;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_item_data', static function (Blueprint $table): void {
            $table->boolean('has_required_tags')->default(false)->nullable(false);
        });

        // Backfill
        ItemData::query()
            ->whereJsonLength('data->stdItem->RequiredTags', '>', 0)
            ->update(['has_required_tags' => true]);

        DB::statement(
            'CREATE INDEX game_item_data_bespoke_vehicle_tags_gin_idx ON game_item_data USING gin (bespoke_vehicle_tags jsonb_path_ops)  WHERE bespoke_vehicle_tags IS NOT NULL'
        );
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS game_item_data_bespoke_vehicle_tags_gin_idx');

        Schema::table('game_item_data', static function (Blueprint $table): void {
            $table->dropColumn('has_required_tags');
        });
    }
};
