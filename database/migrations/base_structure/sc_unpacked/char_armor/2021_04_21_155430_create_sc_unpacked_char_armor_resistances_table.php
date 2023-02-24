<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedCharArmorResistancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_char_armor_resistances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('char_armor_id');
            $table->string('type');
            $table->double('multiplier');
            $table->double('threshold');

            $table->foreign('char_armor_id', 'armor_resistance_item_foreign')
                ->references('id')
                ->on('star_citizen_unpacked_char_armor')
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
        Schema::dropIfExists('star_citizen_unpacked_char_armor_resistances');
    }
}
