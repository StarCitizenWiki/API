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
        Schema::create('sc_clothing_resistances', function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid');
            $table->string('type');
            $table->double('multiplier')->nullable();
            $table->double('threshold')->nullable();
            $table->timestamps();

            $table->foreign('item_uuid', 'fK_sc_c_res_item_uuid')
                ->references('uuid')
                ->on('sc_items')
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
        Schema::dropIfExists('sc_clothing_resistances');
    }
};
