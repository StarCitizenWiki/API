<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipShieldAbsorptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_shield_absorptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_shield_id');
            $table->string('type');
            $table->unsignedDouble('min');
            $table->unsignedDouble('max');
            $table->timestamps();

            $table->foreign('ship_shield_id', 'ship_shield_id_shield_item_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_shields')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('star_citizen_unpacked_ship_shield_absorptions');
    }
}
