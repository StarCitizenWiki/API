<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShopItemRentalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_shop_item_rental', function (Blueprint $table) {
            $table->id();
            $table->string('item_uuid');
            $table->string('shop_uuid');

            $table->float('percentage_1');
            $table->float('percentage_3');
            $table->float('percentage_7');
            $table->float('percentage_30');

            $table->string('version');

            $table->unique(['item_uuid', 'shop_uuid'], 'star_citizen_unpacked_shop_item_rental_unique');
            $table->index('item_uuid');
            $table->index('shop_uuid');
            $table->index('version');

            $table->foreign('item_uuid', 'item_rental_uuid_shop')
                ->references('uuid')
                ->on('star_citizen_unpacked_items')
                ->onDelete('cascade');

            $table->foreign('shop_uuid', 'shop_rental_uuid_shop')
                ->references('uuid')
                ->on('star_citizen_unpacked_shops')
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
        Schema::dropIfExists('star_citizen_unpacked_shop_item_rental');
    }
}
