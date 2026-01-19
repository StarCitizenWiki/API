<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('game_item_data_entity_tag', static function (Blueprint $table) {
            $table->unsignedBigInteger('item_data_id');
            $table->unsignedBigInteger('entity_tag_id');

            $table->foreign('item_data_id')
                ->references('id')
                ->on('game_item_data')
                ->onDelete('cascade');

            $table->foreign('entity_tag_id')
                ->references('id')
                ->on('game_entity_tags')
                ->onDelete('cascade');

            $table->primary(['item_data_id', 'entity_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('game_item_data_entity_tag');
    }
};
