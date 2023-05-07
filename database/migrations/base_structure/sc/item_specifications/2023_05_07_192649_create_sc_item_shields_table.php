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
        Schema::create('sc_item_shields', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();
            $table->unsignedDouble('max_shield_health');
            $table->unsignedDouble('max_shield_regen');
            $table->unsignedDouble('decay_ratio');
            $table->unsignedDouble('downed_regen_delay');
            $table->unsignedDouble('damage_regen_delay');
            $table->unsignedDouble('max_reallocation');
            $table->unsignedDouble('reallocation_rate');
            $table->timestamps();

            $table->foreign('item_uuid', 'fk_s_i_shi_item_uuid')
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
        Schema::dropIfExists('sc_item_shields');
    }
};
