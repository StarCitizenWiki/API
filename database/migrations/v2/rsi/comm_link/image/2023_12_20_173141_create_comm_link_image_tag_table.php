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
        Schema::create('comm_link_image_tag', static function (Blueprint $table) {
            $table->foreignId('image_id')
                ->constrained('comm_link_images')
                ->cascadeOnDelete();
            $table->foreignId('tag_id')
                ->constrained('comm_link_image_tags')
                ->cascadeOnDelete();

            $table->unique(['image_id', 'tag_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comm_link_image_tag');
    }
};
