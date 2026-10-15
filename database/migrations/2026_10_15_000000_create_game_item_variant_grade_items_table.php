<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_item_variant_grade_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('item_data_id')
                ->constrained('game_item_data')
                ->cascadeOnDelete();

            $table->foreignId('grade_item_data_id')
                ->constrained('game_item_data')
                ->cascadeOnDelete();

            $table->unique(['item_data_id', 'grade_item_data_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_item_variant_grade_items');
    }
};
