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
        Schema::create('game_version_diffs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('from_version_id')->constrained('game_versions')->cascadeOnDelete();
            $table->foreignId('to_version_id')->constrained('game_versions')->cascadeOnDelete();
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('change_type');
            $table->json('column_changes')->nullable();
            $table->json('data_changes')->nullable();

            $table->unique(['from_version_id', 'to_version_id', 'entity_type', 'entity_id'], 'version_diff_unique');
            $table->index(['to_version_id', 'entity_type', 'change_type'], 'version_diff_filter_index');
            $table->index(['entity_type', 'entity_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_version_diffs');
    }
};
