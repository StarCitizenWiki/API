<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShopItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_shop_item', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('item_id');
            $table->string('item_uuid');
            $table->string('shop_uuid');
            $table->double('base_price');
            $table->double('base_price_offset');
            $table->double('max_discount');
            $table->double('max_premium');
            $table->double('inventory');
            $table->double('optimal_inventory');
            $table->double('max_inventory');
            $table->boolean('auto_restock');
            $table->boolean('auto_consume');
            $table->double('refresh_rate')->nullable();
            $table->boolean('buyable');
            $table->boolean('sellable');
            $table->boolean('rentable');
            $table->timestamps();

            $table->primary(['shop_id', 'item_id', 'item_uuid']);
            $table->index('item_uuid');
            $table->index('shop_uuid');

            $table->foreign('item_uuid', 'item_uuid_shop')
                ->references('uuid')
                ->on('star_citizen_unpacked_items')
                ->onDelete('cascade');

            $table->foreign('shop_uuid', 'shop_uuid_shop')
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
        Schema::dropIfExists('star_citizen_unpacked_shop_item');
    }
}
