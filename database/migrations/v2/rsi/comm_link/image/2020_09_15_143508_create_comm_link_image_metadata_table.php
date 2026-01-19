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
        Schema::create(
            'comm_link_image_metadata',
            static function (Blueprint $table) {
                $table->id();
                $table->foreignId('comm_link_image_id')
                    ->constrained('comm_link_images')
                    ->cascadeOnDelete();

                $table->unsignedBigInteger('size')->nullable();
                $table->string('mime')->nullable();
                $table->dateTime('last_modified')->nullable();

                $table->timestamps();
                $table->unique('comm_link_image_id');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comm_link_image_metadata');
    }
};
