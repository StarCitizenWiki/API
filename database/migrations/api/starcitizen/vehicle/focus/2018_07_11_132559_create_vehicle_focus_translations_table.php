<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehicleFocusTranslationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'vehicle_focus_translations',
            function (Blueprint $table) {
                $table->unsignedInteger('language_id');
                $table->unsignedInteger('focus_id');
                $table->string('focus');
                $table->timestamps();

                $table->primary(['language_id', 'focus_id']);
                $table->foreign('language_id')->references('id')->on('languages')->onDelete('cascade');
                $table->foreign('focus_id')->references('id')->on('foci')->onDelete('cascade');
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
        Schema::dropIfExists('vehicle_focus_translations');
    }
}
