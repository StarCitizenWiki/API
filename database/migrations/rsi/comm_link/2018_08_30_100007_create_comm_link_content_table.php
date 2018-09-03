<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommLinkContentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'comm_link_content',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('comm_link_id');
                $table->json('content');
                $table->timestamps();

                $table->foreign('comm_link_id')->references('id')->on('comm_links')->onDelete('cascade');
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
        Schema::dropIfExists('comm_link_content');
    }
}
