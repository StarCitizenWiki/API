<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipMissileDamagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_missile_damages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_missile_id');
            $table->string('name');
            $table->unsignedDouble('damage');

            $table->timestamps();

            $table->foreign('ship_missile_id', 'ship_missile_damage_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_missiles')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_missile_damages');
    }
}
