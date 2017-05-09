<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCelestialObjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('Celestial_Objects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('exclude')->default(false);
            $table->timestamps();
            $table->integer('cig_id');
            $table->dateTime('cig_time_modified');
            $table->enum('type', ['JUMPPOINT', 'STAR', 'ASTEROID_BELT', 'MANMADE', 'PLANET', 'LZ', 'SATELLITE']);
            $table->string('designation');
            $table->string('name')->nullable();
            $table->string('code');
            $table->decimal('age');
            $table->decimal('distance');
            $table->decimal('latitude');
            $table->decimal('longitude');
            $table->decimal('axial_tilt');
            $table->decimal('orbit_period')->nullable();
            $table->string('description');
            $table->string('info_url')->nullable();
            $table->boolean('habitable')->nullable();
            $table->boolean('fairchanceact')->nullable();
            $table->boolean('show_orbitlines')->nullable();
            $table->boolean('show_label')->nullable();
            $table->string('appearance');
            $table->integer('sensor_population');
            $table->integer('sensor_economy');
            $table->integer('sensor_danger');
            //TODO Shader Data as Table ShaderData (see BACCHUS.STARS.BACCHUSA)
            $table->string('shader_data')->nullable();
            $table->decimal('size');
            $table->integer('parent_id')->nullable();
            $table->integer('subtype_id')->nullable();
            $table->string('subtype_name')->nullable();

            //TODO affiliation as new table
            $table->integer('affiliation_id');
            $table->string('affiliation_name');
            $table->string('affiliation_code');
            $table->string('affiliation_color');
            $table->integer('affiliation_membership_id');

            //TODO population as new table (content not defined yet)
            $table->string('population');

            $table->json('sourcedata');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('Celestial_Objects');
    }
}
