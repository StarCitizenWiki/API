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
        Schema::create('sc_item_qigs', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();

            $table->unsignedDouble('jammer_range')->nullable();
            $table->unsignedDouble('interdiction_range')->nullable();
            $table->unsignedDouble('charge_duration')->nullable();
            $table->unsignedDouble('discharge_duration')->nullable();
            $table->unsignedDouble('cooldown_duration')->nullable();

            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_i_qig_item_uuid')
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
        Schema::dropIfExists('sc_item_qigs');
    }
};
