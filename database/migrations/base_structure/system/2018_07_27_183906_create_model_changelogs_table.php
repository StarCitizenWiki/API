<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateModelChangelogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'model_changelogs',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('type');
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('changelog_id');
                $table->string('changelog_type');
                $table->timestamps();
            }
        );

        if (config('database.connection') === 'mysql') {
            DB::statement('ALTER TABLE model_changelogs ADD COLUMN changelog LONGBLOB null AFTER type');
        } else {
            DB::statement('ALTER TABLE model_changelogs ADD COLUMN changelog BLOB null');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('model_changelogs');
    }
}
