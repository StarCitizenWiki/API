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
        Schema::create('sc_item_ports', function (Blueprint $table) {
            $table->id();
            $table->string('item_uuid');

            $table->string('name');
            $table->string('display_name');
            $table->uuid('equipped_item_uuid')->nullable();
            $table->unsignedInteger('min_size')->nullable();
            $table->unsignedInteger('max_size')->nullable();
            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_i_por_item_uuid')
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
        Schema::dropIfExists('sc_item_ports');
    }
};
