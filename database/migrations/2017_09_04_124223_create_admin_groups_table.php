<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateAdminGroupTable
 */
class CreateAdminGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'admin_groups',
            function (Blueprint $table) {
                $table->unsignedInteger('admin_id')->nullable();
                $table->foreign('admin_id')->references('id')->on('admins');

                $table->unsignedInteger('group_id')->nullable();
                $table->foreign('group_id')->references('id')->on('groups');

                $table->timestamps();
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
        Schema::dropIfExists('admin_groups');
    }
}
