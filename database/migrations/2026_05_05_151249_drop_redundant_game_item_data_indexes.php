<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // #3 — [item_id, game_version_id] subsumed by item_data_version_unique (May 3)
        // $this->dropIndexIfExists('game_item_data', ['item_id', 'game_version_id']);
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->dropIndex(['item_id', 'game_version_id']);
        });

        // #2 — game_version_id (single) subsumed by any game_version_id-leading composite
        // $this->dropIndexIfExists('game_item_data', 'game_version_id');
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->dropIndex(['game_version_id']);
        });

        // #7 — classification (single) subsumed by game_item_data_version_classification_idx
        // $this->dropIndexIfExists('game_item_data', 'classification');
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->dropIndex(['classification']);
        });

        // #8 — [type, sub_type] subsumed by version_type_idx + version_sub_type_idx
        // $this->dropIndexIfExists('game_item_data', ['type', 'sub_type']);
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->dropIndex(['type', 'sub_type']);
        });

        // #9 — [manufacturer_id, type] subsumed by version_manufacturer_idx + version_type_idx
        // $this->dropIndexIfExists('game_item_data', ['manufacturer_id', 'type']);
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->dropIndex(['manufacturer_id', 'type']);
        });
    }

    public function down(): void
    {
        // #3 — [item_id, game_version_id] subsumed by item_data_version_unique (May 3)
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->index(['item_id', 'game_version_id']);
        });

        // #2 — game_version_id (single) subsumed by any game_version_id-leading composite
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->index(['game_version_id']);
        });

        // #7 — classification (single) subsumed by game_item_data_version_classification_idx
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->index(['classification']);
        });

        // #8 — [type, sub_type] subsumed by version_type_idx + version_sub_type_idx
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->index(['type', 'sub_type']);
        });

        // #9 — [manufacturer_id, type] subsumed by version_manufacturer_idx + version_type_idx
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->index(['manufacturer_id', 'type']);
        });
    }
};
