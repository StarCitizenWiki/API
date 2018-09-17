<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCelestialObjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('celestial_object', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('exclude')->default(false);
            $table->timestamps();
            $table->unsignedInteger('cig_id');
            $table->unsignedInteger('starsystem_id');
            $table->dateTime('cig_time_modified');
            $table->enum('type', ['JUMPPOINT', 'STAR', 'ASTEROID_BELT', 'ASTEROID_FIELD', 'MANMADE', 'PLANET', 'LZ',
                                  'SATELLITE', 'POI', 'BLACKHOLE']);

            $table->string('designation');
            $table->string('name')->nullable();
            $table->string('code');
            $table->decimal('age')->nullable();
            $table->decimal('distance')->nullable();
            $table->decimal('latitude')->nullable();
            $table->decimal('longitude')->nullable();
            $table->decimal('axial_tilt')->nullable();
            $table->decimal('orbit_period')->nullable();
            $table->string('info_url')->nullable();
            $table->boolean('habitable')->nullable();
            $table->boolean('fairchanceact')->nullable();
            $table->string('appearance');
            $table->integer('sensor_population');
            $table->integer('sensor_economy');
            $table->integer('sensor_danger');
            $table->decimal('size')->nullable();
            $table->integer('parent_id')->nullable();
            $table->unsignedInteger('subtype_id')->nullable();
            $table->unsignedInteger('affiliation_id')->nullable();

            $table->foreign('subtype_id')->references('id')->on('celestial_object_subtype')->onDelete('cascade')->nullable();
            $table->foreign('affiliation_id')->references('id')->on('affiliation')->onDelete('cascade')->nullable();
            $table->foreign('starsystem_id')->references('id')->on('starsystem')->onDelete('cascade')->nullable();

            $table->unique('cig_id');
            $table->unique('code');
            $table->index('code');

            //TODO add additionals Fields when CIG deliver content:
            //  population (people?), texture(images), model(3dModel)
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // also remove old celestial_objects
        Schema::dropIfExists('celestial_objects');
        Schema::dropIfExists('celestial_object');
    }
}
