<?php declare(strict_types = 1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateVehicleTypeTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vehicle_type_translations', function (Blueprint $table) {
            $table->unsignedInteger('language_id');
            $table->unsignedInteger('vehicle_type_id');
            $table->string('translation');
            $table->timestamps();

            $table->primary(['language_id', 'vehicle_type_id'], 'vehicle_type_translations_primary');
            $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
            $table->foreign('vehicle_type_id')->references('id')->on('vehicle_types')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vehicle_type_translations');
    }
}
