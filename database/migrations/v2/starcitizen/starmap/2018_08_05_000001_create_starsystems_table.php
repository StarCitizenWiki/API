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
            'starmap_starsystems',
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('cig_id');
                $table->char('code', 20);

                $table->string('status');

                $table->string('info_url')->nullable();

                $table->string('name');
                $table->string('type');

                $table->decimal('position_x');
                $table->decimal('position_y');
                $table->decimal('position_z');

                $table->decimal('frost_line')->nullable();
                $table->decimal('habitable_zone_inner')->nullable();
                $table->decimal('habitable_zone_outer')->nullable();

                $table->decimal('aggregated_size');
                $table->decimal('aggregated_population');
                $table->decimal('aggregated_economy');
                $table->unsignedInteger('aggregated_danger');

                $table->dateTime('time_modified');

                $table->json('translation')->nullable();
                $table->timestamps();
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('starmap_starsystems');
    }
};
