<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class CreateAdminsTable
 */
class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'users',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('username')->unique();
                $table->string('email')->nullable()->unique();
                $table->boolean('blocked');
                $table->string('provider');
                $table->integer('provider_id');
                $table->string('api_token', 60)->unique();
                $table->timestamp('last_login')->nullable();
                $table->rememberToken();
                $table->timestamps();

                $table->unique(['provider_id', 'provider']);
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
        Schema::drop('users');
    }
}
