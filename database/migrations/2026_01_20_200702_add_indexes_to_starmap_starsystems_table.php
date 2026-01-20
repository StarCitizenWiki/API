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
        Schema::table('starmap_starsystems', function (Blueprint $table) {
            $table->unique('code');
            $table->unique('cig_id');
            $table->index('status');
            $table->index('type');
            $table->index(['status', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('starmap_starsystems', function (Blueprint $table) {
            $table->dropIndex(['status', 'type']);
            $table->dropIndex(['type']);
            $table->dropIndex(['status']);
            $table->dropUnique(['cig_id']);
            $table->dropUnique(['code']);
        });
    }
};
