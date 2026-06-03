<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comm_links', static function (Blueprint $table) {
            if (Schema::hasIndex('comm_links', 'comm_links_created_at_index')) {
                $table->dropIndex('comm_links_created_at_index');
            }
        });

        Schema::table('comm_link_image_tag', static function (Blueprint $table) {
            if (! Schema::hasIndex('comm_link_image_tag', 'comm_link_image_tag_tag_id_index')) {
                $table->index('tag_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('comm_link_image_tag', static function (Blueprint $table) {
            $table->dropIndex(['tag_id']);
        });

        Schema::table('comm_links', static function (Blueprint $table) {
            $table->index('created_at');
        });
    }
};
