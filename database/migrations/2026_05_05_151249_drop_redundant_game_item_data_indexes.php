<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('game_item_data', static function (Blueprint $table) {
            // [item_id, game_version_id] subsumed by item_data_version_unique
            $table->dropIndex(['item_id', 'game_version_id']);

            // game_version_id (single) subsumed by any game_version_id-leading composite
            $table->dropIndex(['game_version_id']);

            // classification (single) subsumed by game_item_data_version_classification_idx
            $table->dropIndex(['classification']);

            // [type, sub_type] subsumed by version_type_idx + version_sub_type_idx
            $table->dropIndex(['type', 'sub_type']);

            // [manufacturer_id, type] subsumed by version_manufacturer_idx + version_type_idx
            $table->dropIndex(['manufacturer_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->index(['item_id', 'game_version_id']);
            $table->index(['game_version_id']);
            $table->index(['classification']);
            $table->index(['type', 'sub_type']);
            $table->index(['manufacturer_id', 'type']);
        });
    }
};
