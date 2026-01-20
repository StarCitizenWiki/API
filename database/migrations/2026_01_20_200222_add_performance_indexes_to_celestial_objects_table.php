<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('starmap_celestial_objects', function (Blueprint $table) {
            $table->index('name');
            $table->index('designation');
            $table->index('type');
            $table->index('starsystem_id');
            $table->index('cig_id');
        });

        Schema::table('starmap_starsystems', function (Blueprint $table) {
            $table->index('name');
            $table->index('cig_id');
        });
    }

    public function down(): void
    {
        Schema::table('starmap_celestial_objects', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['designation']);
            $table->dropIndex(['type']);
            $table->dropIndex(['starsystem_id']);
            $table->dropIndex(['cig_id']);
        });

        Schema::table('starmap_starsystems', function (Blueprint $table) {
            $table->dropIndex(['name']);
            $table->dropIndex(['cig_id']);
        });
    }
};
