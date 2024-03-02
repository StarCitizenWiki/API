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
        Schema::create('sc_item_bomb_damages', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bomb_id');
            $table->string('name');
            $table->unsignedDouble('damage');

            $table->timestamps();

            $table->foreign('bomb_id', 'fk_sc_i_b_dam_bomb_id')
                ->references('id')
                ->on('sc_item_bombs')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_item_bomb_damages');
    }
};
