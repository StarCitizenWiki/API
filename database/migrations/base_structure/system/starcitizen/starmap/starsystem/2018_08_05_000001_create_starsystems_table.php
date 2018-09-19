<?php declare(strict_types = 1);

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
    public function up()
    {
        Schema::create(
            'starsystems',
            function (Blueprint $table) {
                $table->increments('id');
                $table->char('code', 20)->unique();
                $table->boolean('exclude')->default(false);
                $table->unsignedInteger('cig_id');
                $table->string('status');
                $table->dateTime('cig_time_modified');
                $table->string('type');
                $table->string('name');
                $table->decimal('position_x');
                $table->decimal('position_y');
                $table->decimal('position_z');
                $table->string('info_url')->nullable();

                $table->decimal('aggregated_size');
                $table->decimal('aggregated_population');
                $table->decimal('aggregated_economy');
                $table->unsignedInteger('aggregated_danger');

                $table->unsignedInteger('affiliation_id')->nullable();
                $table->timestamps();

                $table->foreign('affiliation_id')->references('id')->on('affiliations')->onDelete('cascade');
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
