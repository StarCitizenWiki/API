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
            'starmap_celestial_objects',
            static function (Blueprint $table) {
                $table->id();

                $table->unsignedBigInteger('cig_id');
                $table->unsignedBigInteger('starsystem_id');

                $table->string('age')->nullable();
                $table->string('appearance')->nullable();
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
                $table->string('orbit_period')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->unsignedInteger('sensor_danger');
                $table->unsignedInteger('sensor_economy');
                $table->unsignedInteger('sensor_population');

                $table->string('size')->nullable();
                $table->unsignedBigInteger('subtype_id')->nullable();

                $table->string('type');

                $table->dateTime('time_modified');
                $table->json('translation')->nullable();
                $table->timestamps();

                $table->unique('cig_id');
                $table->unique('code');
                $table->index('code');
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('starmap_celestial_objects');
    }
};
