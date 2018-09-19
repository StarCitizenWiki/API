<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCelestialObjectTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'celestial_object_translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->char('locale_code', 5);
                $table->unsignedInteger('celestial_object_id');
                $table->text('translation');
                $table->timestamps();

                $table->foreign('celestial_object_id')->references('id')->on('celestial_objects')->onDelete('cascade');
                $table->foreign('locale_code')->references('locale_code')->on('languages')->onDelete('cascade');

                $table->unique(['locale_code', 'celestial_object_id'], 'celestial_object_translation_primary');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('celestial_object_translations');
    }
}
