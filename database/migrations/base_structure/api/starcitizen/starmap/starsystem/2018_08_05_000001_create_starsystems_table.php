<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStarsystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'starsystems',
            function (Blueprint $table) {
                $table->id('id');

                $table->unsignedBigInteger('cig_id')->unique();
                $table->char('code', 20)->unique();

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

                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('starsystems');
    }
}
