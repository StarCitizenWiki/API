<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommLinksChangedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'comm_links_changed',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('comm_link_id');
                $table->boolean('had_content');
                $table->enum('type', ['update', 'creation']);

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
        Schema::dropIfExists('comm_links_changed');
    }
}
