<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShipFocusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'ship_focus',
            function (Blueprint $table) {
                $table->unsignedInteger('ship_id');
                $table->unsignedInteger('vehicle_focus_id');
                $table->timestamps();

                $table->primary(['ship_id', 'focus_id']);
                $table->foreign('ship_id')->references('id')->on('ships')->onDelete('cascade');
                $table->foreign('vehicle_focus_id')->references('id')->on('vehicle_foci')->onDelete('cascade');
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
        Schema::dropIfExists('ship_focus');
    }
}
