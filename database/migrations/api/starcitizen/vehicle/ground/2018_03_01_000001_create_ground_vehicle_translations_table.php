<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGroundVehicleTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'ground_vehicle_translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->char('locale_code', 5);
                $table->unsignedInteger('ground_vehicle_id');
                $table->text('translation');
                $table->timestamps();

                $table->unique(['locale_code', 'ground_vehicle_id'], 'ground_vehicle_translations_primary');
                $table->foreign('locale_code')->references('locale_code')->on('languages')->onDelete('cascade');
                $table->foreign('ground_vehicle_id')->references('id')->on('ground_vehicles')->onDelete('cascade');
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
        Schema::dropIfExists('ground_vehicle_translations');
    }
}
