<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateGalactapediaArticleTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('galactapedia_article_translations', function (Blueprint $table) {
            $table->id();
            $table->char('locale_code', 5);
            $table->unsignedBigInteger('article_id');
            $table->timestamps();

            $table->unique(['locale_code', 'article_id'], 'galactapedia_translations_primary');
            $table->foreign('locale_code')->references('locale_code')->on('languages');
        });

        if (config('database.connection') === 'mysql') {
            DB::statement('ALTER TABLE galactapedia_article_translations ADD COLUMN translation LONGBLOB AFTER galactapedia_article_id');
        } else {
            DB::statement('ALTER TABLE galactapedia_article_translations ADD COLUMN translation BLOB');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @param $table
     * @return void
     */
    public function down($table): void
    {
        Schema::dropIfExists($table);
    }
}
