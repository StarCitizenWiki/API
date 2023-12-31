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
        Schema::dropIfExists('comm_link_image_hashes');
        Schema::create('comm_link_image_hashes', static function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('comm_link_image_id');
            $table->binary('average_hash');
            $table->binary('perceptual_hash');
            $table->binary('difference_hash');
            $table->binary('pdq_hash1');
            $table->binary('pdq_hash2');
            $table->binary('pdq_hash3');
            $table->binary('pdq_hash4');
            $table->smallInteger('pdq_quality');
            $table->timestamps();

            $table->foreign('comm_link_image_id')->references('id')->on('comm_link_images')->onDelete('cascade');
            $table->unique('comm_link_image_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comm_link_image_hashes');
        Schema::create('comm_link_image_hashes', static function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('comm_link_image_id');
            $table->binary('average_hash');
            $table->binary('perceptual_hash');
            $table->binary('difference_hash');
            $table->timestamps();

            $table->foreign('comm_link_image_id')->references('id')->on('comm_link_images')->onDelete('cascade');
            $table->unique('comm_link_image_id');
        });
    }
};
