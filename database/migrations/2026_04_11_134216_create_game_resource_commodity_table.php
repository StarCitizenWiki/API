<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_resource_commodity', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_data_id')->constrained('game_resource_data')->cascadeOnDelete();
            $table->foreignId('commodity_id')->nullable()->constrained('game_commodities')->cascadeOnDelete();
            $table->decimal('weight', 8, 4)->nullable();
            $table->decimal('min_percentage', 8, 4)->nullable();
            $table->decimal('max_percentage', 8, 4)->nullable();
            $table->decimal('probability', 8, 4)->nullable();
            $table->decimal('quality_scale', 8, 4)->nullable();
            $table->decimal('curve_exponent', 8, 4)->nullable();
            $table->timestamps();

            $table->index(['resource_data_id', 'commodity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_resource_commodity');
    }
};
