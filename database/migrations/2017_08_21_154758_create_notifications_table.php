<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Class CreateNotificationsTable
 */
class CreateNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'notifications',
            function (Blueprint $table) {
                $table->increments('id');

                $table->tinyInteger('level')->default(0);
                $table->text('content');
                $table->tinyInteger('order')->default(0);

                $table->boolean('output_status')->default(false);
                $table->boolean('output_email')->default(false);
                $table->boolean('output_index')->default(false);

                $table->dateTime('expires_at');
                $table->dateTime('published_at');

                $table->softDeletes();
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
        Schema::dropIfExists('notifications');
    }
}
