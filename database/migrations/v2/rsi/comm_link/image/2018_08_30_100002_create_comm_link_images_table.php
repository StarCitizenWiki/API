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
            'comm_link_images',
            static function (Blueprint $table) {
                $table->id();
                $table->text('src');
                $table->text('alt'); // Thanks RSI???
                $table->boolean('local')->default(false);
                $table->string('dir')->nullable();
                $table->foreignId('base_image_id')
                    ->nullable()
                    ->constrained('comm_link_images')
                    ->nullOnDelete();
                $table->timestamps();

                $table->index('base_image_id');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comm_link_images');
    }
};
