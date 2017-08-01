<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStarsystemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('starsystems', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->char('code', 20);
            $table->boolean('exclude')->default(false);
            $table->timestamps();
            $table->integer('cig_id');
            $table->string('status');
            $table->dateTime('cig_time_modified');
            $table->string('type');
            $table->string('name');
            $table->decimal('position_x');
            $table->decimal('position_y');
            $table->decimal('position_z');
            $table->string('info_url')->nullable();
            $table->string('description');

            //TODO affiliation as seperate table
            $table->integer('affiliation_id')->nullable();
            $table->string('affiliation_name')->nullable();
            $table->string('affiliation_code')->nullable();
            $table->string('affiliation_color')->nullable();
            $table->integer('affiliation_membership_id')->nullable();

            $table->decimal('aggregated_size');
            $table->decimal('aggregated_population');
            $table->decimal('aggregated_economy');
            $table->integer('aggregated_danger');

            $table->json('sourcedata');

            /* Additional Data:
            $table->double('frost_line');
            $table->decimal('habitable_zone_inner');
            $table->decimal('habitable_zone_outer');

            //TODO Shader Data as Table ShaderData (see BACCHUS.STARS.BACCHUSA)
            $table->string('shader_data_lightColor');
            $table->string('shader_data_starfield_radius');
            $table->string('shader_data_starfield_count');
            $table->string('shader_data_starfield_sizeMin');
            $table->string('shader_data_starfield_sizeMax');
            $table->string('shader_data_starfield_color1');
            $table->string('shader_data_starfield_color2');
            $table->decimal('shader_data_planetsSize_min');
            $table->decimal('shader_data_planetsSize_max');
            $table->integer('shader_data_planetsSize_kFactor');

            // Thubnail Data
            $table->string('thumbnail_slug');
            $table->string('thumbnail_source');
            $table->string('thumbnail_images_post');
            $table->string('thumbnail_images_product_thumb_large');
            $table->string('thumbnail_images_subscribers_vault_thumbnail');

            */
        });
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
