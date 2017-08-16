<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateAdminsTable
 */
class CreateAdminsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'admins',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('username');
                $table->string('password')->default(bcrypt(ADMIN_INTERNAL_PASSWORD));
                $table->boolean('blocked')->default(false);
                $table->timestamp('last_login')->default('01.01.1970 00:00:00');
                $table->rememberToken();
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
        Schema::drop('admins');
    }
}
