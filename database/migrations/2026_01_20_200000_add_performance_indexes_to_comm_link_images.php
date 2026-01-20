<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comm_link_images', function (Blueprint $table) {
            $table->index('created_at', 'comm_link_images_created_at_index');
        });

        Schema::table('comm_link_image_metadata', function (Blueprint $table) {
            $table->index('size', 'comm_link_image_metadata_size_index');
        });

        DB::statement('CREATE INDEX IF NOT EXISTS comm_link_images_base_image_id_null_index ON comm_link_images (base_image_id) WHERE base_image_id IS NULL');
        DB::statement('CREATE INDEX IF NOT EXISTS comm_link_images_src_lower_index ON comm_link_images (LOWER(src))');
    }

    public function down(): void
    {
        Schema::table('comm_link_images', function (Blueprint $table) {
            $table->dropIndex('comm_link_images_created_at_index');
        });

        Schema::table('comm_link_image_metadata', function (Blueprint $table) {
            $table->dropIndex('comm_link_image_metadata_size_index');
        });

        DB::statement('DROP INDEX IF EXISTS comm_link_images_base_image_id_null_index');
        DB::statement('DROP INDEX IF EXISTS comm_link_images_src_lower_index');
    }
};
