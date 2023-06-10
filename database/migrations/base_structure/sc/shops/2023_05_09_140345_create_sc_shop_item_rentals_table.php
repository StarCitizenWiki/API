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
    public function up(): void
    {
        Schema::create('sc_shop_item_rentals', static function (Blueprint $table) {
            $table->uuid('item_uuid');
            $table->uuid('shop_uuid');
            $table->uuid('node_uuid');

            $table->float('percentage_1');
            $table->float('percentage_3');
            $table->float('percentage_7');
            $table->float('percentage_30');

            $table->string('version');

            $table->primary(['shop_uuid', 'item_uuid', 'node_uuid', 'version'], 'sc_s_i_ren_primary');
            $table->index('item_uuid');
            $table->index('shop_uuid');
            $table->index('version');

            $table->foreign('item_uuid', 'fk_sc_s_i_ren_item_uuid')
                ->references('uuid')
                ->on('sc_items')
                ->onDelete('cascade');

            $table->foreign('shop_uuid', 'fk_sc_s_i_ren_shop_uuid')
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
    public function down(): void
    {
        Schema::dropIfExists('sc_shop_item_rentals');
    }
};
