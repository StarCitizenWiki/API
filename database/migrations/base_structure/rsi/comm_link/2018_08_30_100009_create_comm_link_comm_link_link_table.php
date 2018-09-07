<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateAdminAdminGroupsTable
 */
class CreateCommLinkCommLinkLinkTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'comm_link_link',
            function (Blueprint $table) {
                $table->unsignedInteger('comm_link_id');
                $table->unsignedInteger('comm_link_link_id');

                $table->foreign('comm_link_id')->references('id')->on('comm_links');
                $table->foreign('comm_link_link_id')->references('id')->on('comm_link_links');
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
        Schema::dropIfExists('comm_link_link');
    }
}
