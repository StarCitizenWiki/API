<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCelestialObjectTranslationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('celestial_object_translation', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->timestamps();
            $table->char('locale_code', 5);
            $table->unsignedInteger('celestial_object_id');
            $table->text('translation');

            $table->foreign('celestial_object_id')->references('id')->on('celestial_object')->onDelete('cascade');
            $table->foreign('locale_code')->references('locale_code')->on('languages')->onDelete('cascade');

//            $table->unique(['locale_code', 'celestialobject_id'], 'celestial_object_translation_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('celestial_object_translation');
    }
}
