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
        Schema::create('sc_item_hacking_chips', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();

            $table->unsignedDouble('max_charges')->nullable();
            $table->unsignedDouble('duration_multiplier')->nullable();
            $table->unsignedDouble('error_chance')->nullable();

            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_i_h_chi_item_uuid')
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
        Schema::dropIfExists('sc_item_hacking_chips');
    }
};
