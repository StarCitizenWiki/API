<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_resource_data', static function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('game_resources')->cascadeOnDelete();
            $table->foreignId('game_version_id')->constrained('game_versions')->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->string('kind');
            $table->string('tier')->nullable();
            $table->unsignedInteger('signature')->nullable();
            $table->jsonb('data')->nullable();
            $table->timestamps();

            $table->unique(['resource_id', 'game_version_id']);
            $table->index('game_version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_resource_data');
    }
};
