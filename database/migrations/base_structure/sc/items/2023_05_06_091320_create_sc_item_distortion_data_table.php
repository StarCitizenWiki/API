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
        Schema::create('sc_item_distortion_data', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid');

            $table->unsignedDouble('decay_rate')->nullable();
            $table->unsignedDouble('maximum')->nullable();
            $table->unsignedDouble('overload_ratio')->nullable();
            $table->unsignedDouble('recovery_ratio')->nullable();
            $table->unsignedDouble('recovery_time')->nullable();

            $table->foreign('item_uuid', 'sc_i_d_dat_item_uuid')
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
        Schema::dropIfExists('sc_item_distortion_data');
    }
};
