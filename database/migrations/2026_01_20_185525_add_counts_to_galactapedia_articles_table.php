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
        Schema::table('galactapedia_articles', static function (Blueprint $table) {
            $table->unsignedBigInteger('categories_count')->default(0);
            $table->unsignedBigInteger('tags_count')->default(0);
            $table->unsignedBigInteger('templates_count')->default(0);
            $table->unsignedBigInteger('related_articles_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('galactapedia_articles', static function (Blueprint $table) {
            $table->dropColumn(['categories_count', 'tags_count', 'templates_count', 'related_articles_count']);
        });
    }
};
