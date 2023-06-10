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
        Schema::create('sc_item_missile_damages', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('missile_id');
            $table->string('name');
            $table->unsignedDouble('damage');

            $table->timestamps();

            $table->foreign('missile_id', 'fk_sc_i_m_dam_missile_id')
                ->references('id')
                ->on('sc_item_missiles')
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
        Schema::dropIfExists('sc_item_missile_damages');
    }
};
