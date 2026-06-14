<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->double('mass')->nullable()->after('bespoke_vehicle_tags');
            $table->jsonb('event_source')->nullable()->after('mass');
            $table->boolean('is_craftable')->default(false)->after('event_source');
            $table->boolean('is_lootable')->default(false)->after('is_craftable');
        });

        // Backfill mass
        DB::statement("
            UPDATE game_item_data
            SET mass = (data #>> '{stdItem,Mass}')::double precision
            WHERE data #>> '{stdItem,Mass}' IS NOT NULL
        ");

        // Backfill event_source
        DB::statement("
            UPDATE game_item_data
            SET event_source = data->'event_source'
            WHERE jsonb_typeof(data->'event_source') = 'array'
        ");

        // Backfill is_lootable from entity_tag_map
        DB::statement("
            UPDATE game_item_data
            SET is_lootable = true
            WHERE jsonb_typeof(data->'entity_tag_map') = 'array'
            AND EXISTS (
                SELECT 1 FROM jsonb_array_elements(data->'entity_tag_map') t
                WHERE t->>'name' = 'CanGenerateAsLoot'
            )
            AND NOT EXISTS (
                SELECT 1 FROM jsonb_array_elements(data->'entity_tag_map') t
                WHERE t->>'name' = 'CannotGenerateAsLoot'
            )
        ");

        // Backfill is_craftable from blueprint output
        DB::statement('
            UPDATE game_item_data
            SET is_craftable = true
            WHERE item_id IN (
                SELECT gi.id FROM game_items gi
                JOIN game_blueprint_data gbd ON gbd.output_item_uuid = gi.uuid
                WHERE gbd.game_version_id = game_item_data.game_version_id
            )
        ');
    }

    public function down(): void
    {
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->dropColumn(['is_lootable', 'is_craftable', 'event_source', 'mass']);
        });
    }
};
