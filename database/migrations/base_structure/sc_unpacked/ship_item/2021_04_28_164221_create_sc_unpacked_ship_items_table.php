<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_items', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('grade')->nullable();
            $table->string('class')->nullable();
            $table->string('type')->nullable();
            $table->string('version');
            $table->timestamps();

            $table->foreign('uuid', 'ship_item_item_uuid')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_items');
    }
}
