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
            'manufacturers',
            static function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('cig_id');
                $table->string('name');
                $table->string('name_short')->unique();
                $table->timestamps();

                $table->unique('cig_id');
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manufacturers');
    }
};
