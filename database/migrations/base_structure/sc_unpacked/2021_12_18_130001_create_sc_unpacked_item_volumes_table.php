<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedItemVolumesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_item_volumes', function (Blueprint $table) {
            $table->id();
            $table->string('item_uuid');

            $table->unsignedDouble('width');
            $table->unsignedDouble('height');
            $table->unsignedDouble('length');
            $table->unsignedDouble('volume');
            $table->timestamps();

            $table->foreign('item_uuid', 'item_id_volume_foreign')
                ->references('uuid')
                ->on('star_citizen_unpacked_items')
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
        Schema::dropIfExists('star_citizen_unpacked_item_volumes');
    }
}
