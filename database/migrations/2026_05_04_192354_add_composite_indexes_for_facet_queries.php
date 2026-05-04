<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add composite indexes to speed up facet GROUP BY queries and listing queries.
     *
     * The primary optimization is for the items `type` facet which groups 19K rows
     * and takes ~36ms. A covering index on (game_version_id, type) allows an
     * index-only scan avoiding heap fetches.
     */
    public function up(): void
    {
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->index(['game_version_id', 'type'], 'game_item_data_version_type_idx');
            $table->index(['game_version_id', 'sub_type'], 'game_item_data_version_sub_type_idx');
            $table->index(['game_version_id', 'classification'], 'game_item_data_version_classification_idx');
            $table->index(['game_version_id', 'manufacturer_id'], 'game_item_data_version_manufacturer_idx');
        });
    }

    public function down(): void
    {
        Schema::table('game_item_data', static function (Blueprint $table) {
            $table->dropIndex('game_item_data_version_type_idx');
            $table->dropIndex('game_item_data_version_sub_type_idx');
            $table->dropIndex('game_item_data_version_classification_idx');
            $table->dropIndex('game_item_data_version_manufacturer_idx');
        });
    }
};
