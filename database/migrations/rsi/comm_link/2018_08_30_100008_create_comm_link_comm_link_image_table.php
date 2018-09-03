<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateAdminAdminGroupsTable
 */
class CreateCommLinkCommLinkImageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'comm_link_image',
            function (Blueprint $table) {
                $table->unsignedInteger('comm_link_id');
                $table->unsignedInteger('comm_link_image_id');

                $table->foreign('comm_link_id')->references('id')->on('comm_links');
                $table->foreign('comm_link_image_id')->references('id')->on('comm_link_images');
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
        Schema::dropIfExists('comm_link_image');
    }
}
