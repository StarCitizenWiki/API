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
        Schema::create('galactapedia_article_translations', static function (Blueprint $table) {
            $table->id();
            $table->string('locale_code', 25);
            $table->unsignedBigInteger('article_id');
            $table->longText('translation');
            $table->timestamps();

            $table->unique(['locale_code', 'article_id'], 'galactapedia_translations_primary');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('galactapedia_article_translations');
    }
};
