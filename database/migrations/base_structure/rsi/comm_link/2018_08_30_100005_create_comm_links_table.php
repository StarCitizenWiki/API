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
                $table->unsignedBigInteger('cig_id')->unique();

                $table->string('title');
                $table->unsignedBigInteger('comment_count');
                $table->string('url')->nullable();

                $table->string('file');

                $table->unsignedInteger('channel_id');
                $table->unsignedInteger('category_id');
                $table->unsignedInteger('series_id');

                $table->timestamps();


                $table->foreign('channel_id')->references('id')->on('comm_link_channels')->onDelete('cascade');
                $table->foreign('category_id')->references('id')->on('comm_link_categories')->onDelete('cascade');
                $table->foreign('series_id')->references('id')->on('comm_link_series')->onDelete('cascade');
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
