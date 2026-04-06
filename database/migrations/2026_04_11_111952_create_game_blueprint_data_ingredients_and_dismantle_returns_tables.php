<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_blueprint_data_ingredients', static function (Blueprint $table): void {
            $table->unsignedBigInteger('blueprint_data_id');
            $table->unsignedBigInteger('resource_type_id');

            $table->foreign('blueprint_data_id')->references('id')->on('game_blueprint_data')->cascadeOnDelete();
            $table->foreign('resource_type_id')->references('id')->on('game_resource_types')->cascadeOnDelete();

            $table->primary(['blueprint_data_id', 'resource_type_id']);
        });

        Schema::create('game_blueprint_data_dismantle_returns', static function (Blueprint $table): void {
            $table->unsignedBigInteger('blueprint_data_id');
            $table->unsignedBigInteger('resource_type_id');
            $table->decimal('quantity_scu', 10, 4);

            $table->foreign('blueprint_data_id')->references('id')->on('game_blueprint_data')->cascadeOnDelete();
            $table->foreign('resource_type_id')->references('id')->on('game_resource_types')->cascadeOnDelete();

            $table->primary(['blueprint_data_id', 'resource_type_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_blueprint_data_dismantle_returns');
        Schema::dropIfExists('game_blueprint_data_ingredients');
    }
};
