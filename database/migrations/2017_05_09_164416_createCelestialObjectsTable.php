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
        Schema::create('celestial_objects', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->boolean('exclude')->default(false);
            $table->timestamps();
            $table->integer('cig_id');
            $table->integer('cig_system_id');
            $table->dateTime('cig_time_modified');
            $table->enum('type', ['JUMPPOINT', 'STAR', 'ASTEROID_BELT', 'MANMADE', 'PLANET', 'LZ', 'SATELLITE']);
            $table->string('designation');
            $table->string('name')->nullable();
            $table->string('code');
            $table->decimal('age')->nullable();
            $table->decimal('distance')->nullable();
            $table->decimal('latitude')->nullable();
            $table->decimal('longitude')->nullable();
            $table->decimal('axial_tilt')->nullable();
            $table->decimal('orbit_period')->nullable();
            $table->string('description')->nullable();
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
            $table->decimal('size')->nullable();
            $table->integer('parent_id')->nullable();
            //TODO Subtype Data as Table Subtype (see BACCHUS.STARS.BACCHUSA)
            $table->integer('subtype_id')->nullable();
            $table->string('subtype_name')->nullable();
            $table->string('subtype_type')->nullable();

            //TODO affiliation as new table
            $table->integer('affiliation_id')->nullable();
            $table->string('affiliation_name')->nullable();
            $table->string('affiliation_code')->nullable();
            $table->string('affiliation_color')->nullable();
            $table->integer('affiliation_membership_id')->nullable();

            //TODO population as new table (content not defined yet)
            $table->string('population')->nullable();

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
