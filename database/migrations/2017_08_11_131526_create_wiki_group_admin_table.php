<?php declare( strict_types = 1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateAdminGroupsTable
 */
class CreateWikiGroupAdminTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'wiki_group_admin',
            function (Blueprint $table) {
                $table->increments('id');
                $table->integer('admin_id');
                $table->integer('wiki_group_id');
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
        Schema::dropIfExists('wiki_group_admin');
    }
}
