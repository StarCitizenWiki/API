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
            'comm_links_changed',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('comm_link_id');
                $table->boolean('had_content');
                $table->enum('type', ['update', 'creation']);

                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comm_links_changed');
    }
};
