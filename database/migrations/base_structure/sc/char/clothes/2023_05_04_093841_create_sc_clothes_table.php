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
        Schema::create('sc_clothes', function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();
            $table->string('type')->nullable();
            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_clo_item_uuid')
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
        Schema::dropIfExists('sc_clothes');
    }
};
