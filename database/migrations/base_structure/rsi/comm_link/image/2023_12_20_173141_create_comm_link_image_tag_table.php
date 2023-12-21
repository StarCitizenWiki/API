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
            $table->unsignedInteger('image_id');
            $table->unsignedBigInteger('tag_id');

            $table->foreign('image_id')->references('id')->on('comm_link_images')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('comm_link_image_tags')->onDelete('cascade');
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
