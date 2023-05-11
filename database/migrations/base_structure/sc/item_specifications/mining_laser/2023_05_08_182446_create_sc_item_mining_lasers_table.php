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
        Schema::create('sc_item_mining_lasers', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();

            $table->unsignedDouble('power_transfer')->nullable();
            $table->unsignedDouble('optimal_range')->nullable();
            $table->unsignedDouble('maximum_range')->nullable();
            $table->unsignedDouble('extraction_throughput')->nullable();
            $table->unsignedInteger('module_slots')->nullable();

            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_i_m_las_item_uuid')
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
        Schema::dropIfExists('sc_item_mining_lasers');
    }
};
