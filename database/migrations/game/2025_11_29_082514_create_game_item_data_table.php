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
        Schema::create('game_item_data', static function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('item_id');
            $table->unsignedBigInteger('game_version_id')->index();
            $table->unsignedBigInteger('manufacturer_id');
            $table->string('name');
            $table->string('class_name');
            $table->string('type')->nullable();
            $table->string('sub_type')->nullable();
            $table->string('classification')->nullable();
            $table->unsignedInteger('size')->nullable();
            $table->unsignedInteger('grade')->nullable();
            $table->string('class')->nullable();
            $table->unsignedBigInteger('base_id')->nullable();
            $table->jsonb('data');
            $table->timestamps();

            $table->index(['item_id', 'game_version_id']);
            $table->index(['item_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_item_data');
    }
};
