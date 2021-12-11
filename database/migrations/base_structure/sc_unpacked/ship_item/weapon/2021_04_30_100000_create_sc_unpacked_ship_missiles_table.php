<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipMissilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_missiles', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');
            $table->string('uuid')->unique();
            $table->string('signal_type');
            $table->unsignedDouble('lock_time');

            $table->timestamps();

            $table->foreign('ship_item_id', 'missiles_ship_item_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_items')
                ->onDelete('cascade');

            $table->foreign('uuid', 'missiles_ship_item_uuid')
                ->references('uuid')
                ->on('star_citizen_unpacked_ship_items')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_missiles');
    }
}
