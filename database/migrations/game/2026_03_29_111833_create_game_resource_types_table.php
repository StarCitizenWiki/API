<?php

declare(strict_types=1);

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
        Schema::create('game_resource_types', static function (Blueprint $table): void {
            $table->id();
            $table->uuid()->unique();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description');
            $table->uuid('refined_version_uuid')->nullable()->index();
            $table->boolean('validate_default_cargo_box')->default(false);
            $table->boolean('has_default_cargo_containers')->default(false);
            $table->jsonb('box_sizes_scu');
            $table->jsonb('data');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('game_resource_types');
    }
};
