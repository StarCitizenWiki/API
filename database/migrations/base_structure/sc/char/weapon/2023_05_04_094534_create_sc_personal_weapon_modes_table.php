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
        Schema::create('sc_personal_weapon_modes', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid');
            $table->string('mode');
            $table->string('localised');
            $table->string('type');
            $table->unsignedDouble('rounds_per_minute');
            $table->unsignedDouble('ammo_per_shot');
            $table->unsignedDouble('pellets_per_shot');
            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_p_w_mod_item_uuid')
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
        Schema::dropIfExists('sc_personal_weapon_modes');
    }
};
