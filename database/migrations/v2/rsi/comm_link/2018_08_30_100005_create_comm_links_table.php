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
            'comm_links',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cig_id')->unique();

                $table->string('title');
                $table->unsignedBigInteger('comment_count')->default(0);
                $table->string('url')->nullable();

                $table->string('file');

                $table->foreignId('channel_id')
                    ->constrained('comm_link_channels')
                    ->cascadeOnDelete();
                $table->foreignId('category_id')
                    ->constrained('comm_link_categories')
                    ->cascadeOnDelete();
                $table->foreignId('series_id')
                    ->constrained('comm_link_series')
                    ->cascadeOnDelete();

                $table->timestamp('created_at_file')->nullable();

                $table->json('translation')->nullable();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comm_links');
    }
};
