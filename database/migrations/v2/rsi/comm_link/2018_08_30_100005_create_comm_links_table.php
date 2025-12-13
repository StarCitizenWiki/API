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
                $table->unsignedBigInteger('comment_count');
                $table->string('url')->nullable();

                $table->string('file');

                $table->unsignedBigInteger('channel_id');
                $table->unsignedBigInteger('category_id');
                $table->unsignedBigInteger('series_id');

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
