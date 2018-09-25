<?php declare(strict_types = 1);

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
    public function up()
    {
        Schema::create(
            'starsystem_translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->char('locale_code', 5);
                $table->unsignedInteger('starsystem_id');
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
    public function down()
    {
        Schema::dropIfExists('starsystem_translations');
    }
}
