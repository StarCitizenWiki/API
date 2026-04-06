<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_resource_locations', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_data_id')->constrained('game_resource_data')->cascadeOnDelete();
            $table->string('group_name');
            $table->decimal('group_probability', 8, 6);
            $table->decimal('relative_probability', 12, 10);
            $table->string('resource_kind', 50)->nullable();
            $table->foreignId('commodity_id')->nullable()->constrained('game_commodities')->nullOnDelete();
            $table->unsignedInteger('quality_min')->nullable();
            $table->unsignedInteger('quality_max')->nullable();
            $table->unsignedInteger('quality_mean')->nullable();
            $table->unsignedInteger('quality_stddev')->nullable();
            $table->decimal('min_percentage', 8, 4)->nullable();
            $table->decimal('max_percentage', 8, 4)->nullable();
            $table->jsonb('data')->nullable();
            $table->timestamps();

            $table->index('resource_data_id');
            $table->index('commodity_id');
            $table->index('quality_min');
            $table->index('resource_kind');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_resource_locations');
    }
};
