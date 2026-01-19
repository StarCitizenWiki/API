<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateAdminAdminGroupsTable
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            'comm_link_image',
            static function (Blueprint $table) {
                $table->foreignId('comm_link_id')
                    ->constrained('comm_links')
                    ->cascadeOnDelete();
                $table->foreignId('comm_link_image_id')
                    ->constrained('comm_link_images')
                    ->cascadeOnDelete();

                $table->unique(['comm_link_id', 'comm_link_image_id']);
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comm_link_image');
    }
};
