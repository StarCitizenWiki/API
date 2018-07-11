<?php declare(strict_types = 1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class CreateUsersTable
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
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('api_token', 60)->unique();
            $table->string('password', 60);
            $table->integer('requests_per_minute')->unsigned();
            $table->tinyInteger('state')->default(0);
            $table->tinyInteger('receive_notification_level')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('last_login')->default('1970-01-01 00:00:01');
            $table->timestamp('api_token_last_used')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}