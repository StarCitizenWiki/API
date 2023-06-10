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
        Schema::create('sc_personal_weapon_ammunitions', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid');
            $table->unsignedInteger('size');
            $table->unsignedDouble('lifetime');
            $table->unsignedDouble('speed');
            $table->unsignedDouble('range');
            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_p_w_amm_item_uuid')
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
        Schema::dropIfExists('sc_personal_weapon_ammunitions');
    }
};
