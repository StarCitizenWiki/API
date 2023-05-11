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
        Schema::create('sc_item_dimensions', function (Blueprint $table) {
            $table->id();
            $table->string('item_uuid');

            $table->unsignedDouble('width');
            $table->unsignedDouble('height');
            $table->unsignedDouble('length');
            $table->unsignedDouble('volume')->nullable();
            $table->boolean('override');
            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_i_dim_item_uuid')
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
        Schema::dropIfExists('sc_item_dimensions');
    }
};
