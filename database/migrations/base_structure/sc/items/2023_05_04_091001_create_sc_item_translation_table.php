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
        Schema::create('sc_item_translation', static function (Blueprint $table) {
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('item_translation_id');

            $table->foreign('item_id', 'fk_sc_i_tra_item_id')
                ->references('id')
                ->on('sc_items')
                ->onDelete('cascade');

            $table->foreign('item_translation_id', 'fk_sc_i_tra_item_translation_id')
                ->references('id')
                ->on('sc_item_translations')
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
        Schema::dropIfExists('sc_item_translations');
    }
};
