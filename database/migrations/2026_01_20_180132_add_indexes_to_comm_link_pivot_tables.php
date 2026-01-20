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
        Schema::table('comm_link_image', function (Blueprint $table) {
            $table->index('comm_link_image_id');
        });

        Schema::table('comm_link_link', function (Blueprint $table) {
            $table->index('comm_link_link_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comm_link_image', function (Blueprint $table) {
            $table->dropIndex(['comm_link_image_id']);
        });

        Schema::table('comm_link_link', function (Blueprint $table) {
            $table->dropIndex(['comm_link_link_id']);
        });
    }
};
