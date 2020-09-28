<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCelestialObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'celestial_objects',
            function (Blueprint $table) {
                $table->id('id');

                $table->unsignedBigInteger('cig_id');
                $table->unsignedBigInteger('starsystem_id');

                $table->unsignedBigInteger('age')->nullable();
                $table->string('appearance');
                $table->decimal('axial_tilt')->nullable();
                $table->string('code');
                $table->string('designation');
                $table->decimal('distance')->nullable();
                $table->boolean('fairchanceact')->nullable();
                $table->boolean('habitable')->nullable();
                $table->string('info_url')->nullable();
                $table->decimal('latitude')->nullable();
                $table->decimal('longitude')->nullable();
                $table->string('name')->nullable();
                $table->decimal('orbit_period')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedInteger('sensor_danger');
                $table->unsignedInteger('sensor_economy');
                $table->unsignedInteger('sensor_population');

                $table->decimal('size')->nullable();
                $table->unsignedBigInteger('subtype_id')->nullable();

                $table->enum(
                    'type',
                    [
                        'JUMPPOINT',
                        'STAR',
                        'ASTEROID_BELT',
                        'ASTEROID_FIELD',
                        'MANMADE',
                        'PLANET',
                        'LZ',
                        'SATELLITE',
                        'POI',
                        'BLACKHOLE',
                    ]
                );

                $table->dateTime('time_modified');
                $table->timestamps();

                $table->foreign('subtype_id')
                    ->references('id')
                    ->on('celestial_object_subtypes')
                    ->onDelete('cascade');

                $table->foreign('starsystem_id')
                    ->references('id')
                    ->on('starsystems');

                $table->unique('cig_id');
                $table->unique('code');
                $table->index('code');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('celestial_objects');
    }
}
