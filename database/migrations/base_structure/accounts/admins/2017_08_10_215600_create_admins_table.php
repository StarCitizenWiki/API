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
                $table->string('username')->unique();
                $table->string('email')->nullable()->unique();
                $table->boolean('blocked');
                $table->string('provider');
                $table->integer('provider_id')->unique();
                $table->timestamp('last_login')->nullable();
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
