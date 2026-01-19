<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create(
            'starmap_affiliations',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cig_id');

                $table->string('name');
                $table->string('code');
                $table->string('color');
                $table->unsignedBigInteger('membership_id')->nullable();

                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('starmap_affiliations');
    }
};
