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
        Schema::create('sc_item_interaction', static function (Blueprint $table) {
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('interaction_id');

            $table->foreign('item_id', 'sc_i_interaction_item_id')
                ->references('id')
                ->on('sc_items')
                ->onDelete('cascade');

            $table->foreign('interaction_id', 'sc_i_interaction_interaction_id')
                ->references('id')
                ->on('sc_item_interactions')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_item_interaction');
    }
};
