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
        Schema::create('sc_item_salvage_modifiers', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();

            $table->unsignedDouble('salvage_speed_multiplier')->nullable();
            $table->unsignedDouble('radius_multiplier')->nullable();
            $table->unsignedDouble('extraction_efficiency')->nullable();

            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_i_salvagemod_item_uuid')
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
        Schema::dropIfExists('sc_item_salvage_modifiers');
    }
};
