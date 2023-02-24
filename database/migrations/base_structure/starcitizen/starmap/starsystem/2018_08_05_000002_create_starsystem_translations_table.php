<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStarsystemTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'starsystem_translations',
            function (Blueprint $table) {
                $table->id('id');
                $table->char('locale_code', 5);
                $table->unsignedBigInteger('starsystem_id');
                $table->text('translation');
                $table->timestamps();

                $table->foreign('starsystem_id')->references('id')->on('starsystems');
                $table->foreign('locale_code')->references('locale_code')->on('languages');

                $table->unique(['locale_code', 'starsystem_id'], 'starsystem_translation_primary');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('starsystem_translations');
    }
}
