<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'comm_links',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedBigInteger('cig_id');
                $table->unsignedBigInteger('comment_count');

                $table->string('file');

                $table->unsignedInteger('resort_id');
                $table->unsignedInteger('category_id');

                $table->timestamps();


                $table->foreign('resort_id')->references('id')->on('resorts')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
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
        Schema::dropIfExists('comm_links');
    }
}
