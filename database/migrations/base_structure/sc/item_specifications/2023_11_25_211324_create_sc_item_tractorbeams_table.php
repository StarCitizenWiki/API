<?php

declare(strict_types=1);

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
        Schema::create('sc_item_tractorbeams', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();

            $table->unsignedDouble('min_force')->nullable();
            $table->unsignedDouble('max_force')->nullable();
            $table->unsignedDouble('min_distance')->nullable();
            $table->unsignedDouble('max_distance')->nullable();
            $table->unsignedDouble('full_strength_distance')->nullable();
            $table->unsignedDouble('max_angle')->nullable();
            $table->unsignedDouble('max_volume')->nullable();
            $table->unsignedDouble('volume_force_coefficient')->nullable();
            $table->unsignedDouble('tether_break_time')->nullable();
            $table->unsignedDouble('safe_range_value_factor')->nullable();

            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_i_tractorbeam_item_uuid')
                ->references('uuid')
                ->on('sc_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_item_tractorbeams');
    }
};
