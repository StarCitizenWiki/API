<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sc_clothing_radiation_resistances', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid');
            $table->double('maximum_radiation_capacity')->nullable();
            $table->double('radiation_dissipation_rate')->nullable();
            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_c_r_res_item_uuid')
                ->references('uuid')
                ->on('sc_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_clothing_radiation_resistances');
    }
};
