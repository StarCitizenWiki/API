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
            $table->unsignedBigInteger('images_count')->default(0)->after('comment_count');
            $table->unsignedBigInteger('links_count')->default(0)->after('images_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comm_links', function (Blueprint $table) {
            $table->dropColumn(['images_count', 'links_count']);
        });
    }
};
