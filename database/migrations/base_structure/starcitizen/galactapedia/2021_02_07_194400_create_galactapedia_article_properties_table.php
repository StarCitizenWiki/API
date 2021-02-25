<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGalactapediaArticlePropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('galactapedia_article_properties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('article_id');
            $table->string('name');
            $table->text('content');
            $table->timestamps();

            $table->foreign('article_id')->references('id')->on('galactapedia_articles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('galactapedia_article_properties');
    }
}
