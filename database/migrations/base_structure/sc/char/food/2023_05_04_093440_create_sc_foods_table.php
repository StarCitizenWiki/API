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
        Schema::create('sc_foods', function (Blueprint $table) {
            $table->id();
            $table->uuid('item_uuid')->unique();
            $table->unsignedInteger('nutritional_density_rating')->nullable();
            $table->unsignedInteger('hydration_efficacy_index')->nullable();
            $table->string('container_type')->nullable();
            $table->boolean('one_shot_consume')->nullable();
            $table->boolean('can_be_reclosed')->nullable();
            $table->boolean('discard_when_consumed')->nullable();
            $table->timestamps();

            $table->foreign('item_uuid', 'fk_sc_foo_item_uuid')
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
        Schema::dropIfExists('sc_foods');
    }
};
