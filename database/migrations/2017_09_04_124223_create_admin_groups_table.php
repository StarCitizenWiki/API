<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateAdminGroupTable
 */
class CreateAdminGroupsTable extends Migration
{
    const TABLE = 'admin_groups';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            self::TABLE,
            function (Blueprint $table) {
                $table->unsignedInteger('admin_id');
                $table->unsignedInteger('group_id');

                $table->timestamps();
            }
        );

        Schema::table(
            self::TABLE,
            function (Blueprint $table) {
                $table->foreign('admin_id')->references('id')->on('admins');
                $table->foreign('group_id')->references('id')->on('groups');
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
        Schema::dropIfExists(self::TABLE);
    }
}
