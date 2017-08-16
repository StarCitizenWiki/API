<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->boolean('whitelisted')->default(0);
            $table->boolean('blacklisted')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('last_login')->default('01.01.1970 00:00:00');
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
