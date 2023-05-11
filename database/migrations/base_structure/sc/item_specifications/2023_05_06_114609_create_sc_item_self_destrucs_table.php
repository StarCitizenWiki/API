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
        Schema::create('sc_item_self_destrucs', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();

            $table->unsignedDouble('damage');
            $table->unsignedDouble('min_radius');
            $table->unsignedDouble('radius');
            $table->unsignedDouble('phys_radius');
            $table->unsignedDouble('min_phys_radius');
            $table->unsignedDouble('time');
            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_i_s_des_item_uuid')
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
        Schema::dropIfExists('sc_item_self_destrucs');
    }
};
