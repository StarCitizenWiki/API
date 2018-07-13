<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManufacturersTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'manufacturers_translations',
            function (Blueprint $table) {
                $table->unsignedInteger('language_id');
                $table->unsignedInteger('manufacturer_id');
                $table->string('known_for');
                $table->text('description');
                $table->timestamps();

                $table->primary(['language_id', 'manufacturer_id']);
                $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
                $table->foreign('manufacturer_id')->references('id')->on('manufacturers')->onDelete('cascade');
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
        Schema::dropIfExists('manufacturers_translations');
    }
}
