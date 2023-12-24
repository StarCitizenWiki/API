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
        Schema::table('comm_link_images', static function (Blueprint $table) {
            $table->unsignedInteger('base_image_id')->nullable()->after('dir');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comm_link_images', static function (Blueprint $table) {
            $table->dropColumn('base_image_id');
        });
    }
};
