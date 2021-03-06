<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManufacturersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'manufacturers',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('cig_id');
                $table->string('name')->unique();
                $table->string('name_short')->unique();
                $table->timestamps();

                $table->unique('cig_id');
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
        Schema::dropIfExists('manufacturers');
    }
}
