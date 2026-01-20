<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('comm_links', function (Blueprint $table) {
            $table->index('created_at', 'comm_links_created_at_index');
            $table->index('title', 'comm_links_title_index');
            $table->index(['created_at', 'cig_id'], 'comm_links_created_at_cig_id_index');
            $table->index('channel_id', 'comm_links_channel_id_index');
            $table->index('category_id', 'comm_links_category_id_index');
            $table->index('series_id', 'comm_links_series_id_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comm_links', function (Blueprint $table) {
            $table->dropIndex('comm_links_created_at_index');
            $table->dropIndex('comm_links_title_index');
            $table->dropIndex('comm_links_created_at_cig_id_index');
            $table->dropIndex('comm_links_channel_id_index');
            $table->dropIndex('comm_links_category_id_index');
            $table->dropIndex('comm_links_series_id_index');
        });
    }
};
