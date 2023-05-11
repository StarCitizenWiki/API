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
        Schema::create('sc_vehicle_weapons', static function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();
            $table->string('weapon_type')->nullable();
            $table->string('weapon_class')->nullable();
            $table->unsignedDouble('capacity')->nullable();

            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_v_wea_item_uuid')
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
        Schema::dropIfExists('sc_vehicle_weapons');
    }
};
