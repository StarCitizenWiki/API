<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'vehicle_translations',
            function (Blueprint $table) {
                $table->increments('id');
                $table->char('locale_code', 5);
                $table->unsignedInteger('vehicle_id');
                $table->text('translation');
                $table->timestamps();

                $table->unique(['locale_code', 'vehicle_id'], 'vehicle_translations_primary');
                $table->foreign('locale_code')->references('locale_code')->on('languages');
                $table->foreign('vehicle_id')->references('id')->on('vehicles');
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
        Schema::dropIfExists('vehicle_translations');
    }
}
