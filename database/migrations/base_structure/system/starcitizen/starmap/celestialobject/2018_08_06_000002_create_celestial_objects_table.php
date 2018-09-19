<?php declare(strict_types = 1);

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
    public function up()
    {
        Schema::create(
            'celestial_objects',
            function (Blueprint $table) {
                $table->increments('id');
                $table->boolean('exclude')->default(false);
                $table->unsignedInteger('cig_id');
                $table->unsignedInteger('starsystem_id');
                $table->dateTime('cig_time_modified');
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
                $table->timestamps();

                $table->foreign('subtype_id')->references('id')->on('celestial_object_subtypes')->onDelete(
                    'cascade'
                )->nullable();
                $table->foreign('affiliation_id')->references('id')->on('affiliations')->onDelete('cascade')->nullable();
                $table->foreign('starsystem_id')->references('id')->on('starsystems')->onDelete('cascade')->nullable();

                $table->unique('cig_id');
                $table->unique('code');
                $table->index('code');

                //TODO add additionals Fields when CIG deliver content:
                //  population (people?), texture(images), model(3dModel)
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
        Schema::dropIfExists('celestial_objects');
    }
}
