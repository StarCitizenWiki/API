<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_item_variant_groups', function (Blueprint $table) {
            $table->id();

            $table->foreignId('game_version_id')
                ->constrained('game_versions')
                ->cascadeOnDelete();

            $table->string('set_name')->nullable();

            $table->timestamps();

            $table->index(['game_version_id']);
        });

        Schema::create('game_item_set_items', function (Blueprint $table) {
            $table->foreignId('item_data_id')
                ->constrained('game_item_data')
                ->cascadeOnDelete();

            $table->foreignId('set_item_data_id')
                ->constrained('game_item_data')
                ->cascadeOnDelete();

            $table->primary(['item_data_id', 'set_item_data_id']);
        });

        Schema::create('game_item_variant_group_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('variant_group_id')
                ->constrained('game_item_variant_groups')
                ->cascadeOnDelete();

            $table->foreignId('item_data_id')
                ->constrained('game_item_data')
                ->cascadeOnDelete();

            $table->string('variant_name')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_base')->default(false);

            $table->unique(['variant_group_id', 'item_data_id']);
            $table->unique(['item_data_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_item_variant_group_items');
        Schema::dropIfExists('game_item_set_items');
        Schema::dropIfExists('game_item_variant_groups');
    }
};
