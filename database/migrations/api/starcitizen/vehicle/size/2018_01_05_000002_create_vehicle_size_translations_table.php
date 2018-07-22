<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleSizeTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'vehicle_size_translations',
            function (Blueprint $table) {
                $table->unsignedInteger('language_id');
                $table->unsignedInteger('vehicle_size_id');
                $table->string('translation');
                $table->timestamps();

                $table->primary(['language_id', 'vehicle_size_id'], 'vehicle_size_translations_primary');
                $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
                $table->foreign('vehicle_size_id')->references('id')->on('vehicle_sizes')->onDelete('cascade');
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
        Schema::dropIfExists('vehicle_size_translations');
    }
}
