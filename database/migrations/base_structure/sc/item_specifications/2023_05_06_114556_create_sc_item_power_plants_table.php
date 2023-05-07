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
        Schema::create('sc_item_power_plants', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();
            $table->unsignedDouble('power_output');
            $table->timestamps();

            $table->foreign('item_uuid', 'fk_s_i_p_pla_item_uuid')
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
        Schema::dropIfExists('sc_item_power_plants');
    }
};
