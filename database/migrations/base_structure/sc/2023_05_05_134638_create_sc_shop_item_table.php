<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sc_shop_item', function (Blueprint $table) {
            $table->unsignedBigInteger('shop_id');
            $table->unsignedBigInteger('item_id');
            $table->uuid('item_uuid');
            $table->uuid('shop_uuid');
            $table->uuid('node_uuid');
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
            $table->string('version');

            $table->primary(['shop_id', 'item_id', 'item_uuid', 'node_uuid', 'version'], 'sc_s_i_shop_item_primary');
            $table->index('item_uuid');
            $table->index('shop_uuid');
            $table->index('version');

            $table->foreign('item_uuid', 'fk_sc_s_i_item_uuid')
                ->references('uuid')
                ->on('sc_items')
                ->onDelete('cascade');

            $table->foreign('shop_uuid', 'fk_sc_s_i_shop_uuid')
                ->references('uuid')
                ->on('sc_shops')
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
        Schema::dropIfExists('sc_shop_item');
    }
};
