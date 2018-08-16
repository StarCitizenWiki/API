<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStarsystemTranslationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('starsystem_translation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->char('locale_code', 5);
            $table->unsignedInteger('cig_id');
            $table->text('translation');

            $table->foreign('cig_id')->references('cig_id')->on('starsystem')->onDelete('cascade');
            $table->foreign('locale_code')->references('locale_code')->on('languages')->onDelete('cascade');

            $table->unique(['locale_code', 'cig_id'], 'starsystem_translation_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('starsystem_translation');
    }
}
