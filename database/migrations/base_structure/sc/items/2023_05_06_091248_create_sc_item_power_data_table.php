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
        Schema::create('sc_item_power_data', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid');

            $table->unsignedDouble('power_base')->nullable();
            $table->unsignedDouble('power_draw')->nullable();
            $table->boolean('throttleable')->default(false);
            $table->boolean('overclockable')->default(false);
            $table->unsignedDouble('overclock_threshold_min')->nullable();
            $table->unsignedDouble('overclock_threshold_max')->nullable();
            $table->unsignedDouble('overclock_performance')->nullable();
            $table->unsignedDouble('overpower_performance')->nullable();
            $table->unsignedDouble('power_to_em')->nullable();
            $table->unsignedDouble('decay_rate_em')->nullable();
            $table->timestamps();

            $table->foreign('item_uuid', 'sc_i_p_dat_item_uuid')
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
        Schema::dropIfExists('sc_item_power_data');
    }
};
