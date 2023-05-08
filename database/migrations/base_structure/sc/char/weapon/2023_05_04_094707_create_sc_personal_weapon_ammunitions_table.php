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
            $table->unsignedBigInteger('weapon_id');
            $table->unsignedInteger('size');
            $table->unsignedDouble('lifetime');
            $table->unsignedDouble('speed');
            $table->unsignedDouble('range');
            $table->timestamps();

            $table->foreign('weapon_id', 'fk_sc_p_w_amm_weapon_id')
                ->references('id')
                ->on('sc_personal_weapons')
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
