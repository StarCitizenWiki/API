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
        Schema::create('sc_item_iron_sights', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();

            $table->unsignedDouble('default_range')->nullable();
            $table->unsignedDouble('max_range')->nullable();
            $table->unsignedDouble('range_increment')->nullable();
            $table->unsignedDouble('auto_zeroing_time')->nullable();
            $table->unsignedInteger('zoom_scale')->nullable();
            $table->unsignedInteger('zoom_time_scale')->nullable();

            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_i_i_sig_item_uuid')
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
        Schema::dropIfExists('sc_item_iron_sights');
    }
};
