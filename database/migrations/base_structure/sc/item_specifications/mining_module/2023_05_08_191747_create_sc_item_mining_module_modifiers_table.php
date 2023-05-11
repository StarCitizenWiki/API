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
        Schema::create('sc_item_mining_module_modifiers', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('mining_module_id');

            $table->string('name');
            $table->string('modifier');

            $table->timestamps();

            $table->foreign('mining_module_id', 'fk_sc_i_m_m_mod_mining_laser_id')
                ->references('id')
                ->on('sc_item_mining_modules')
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
        Schema::dropIfExists('sc_item_mining_module_modifiers');
    }
};
