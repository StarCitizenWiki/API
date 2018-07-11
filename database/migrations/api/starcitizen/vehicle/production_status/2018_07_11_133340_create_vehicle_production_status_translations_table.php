<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleProductionStatusTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'vehicle_production_status_translations',
            function (Blueprint $table) {
                $table->unsignedInteger('language_id');
                $table->unsignedInteger('production_status_id');
                $table->string('status');
                $table->timestamps();

                $table->primary(['language_id', 'production_status_id']);
                $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
                $table->foreign('production_status_id')->references('id')->on('production_status')->onDelete('cascade');
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
        Schema::dropIfExists('vehicle_production_status_translations');
    }
}
