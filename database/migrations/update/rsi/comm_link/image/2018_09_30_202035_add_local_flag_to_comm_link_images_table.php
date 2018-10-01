<?php declare(strict_types = 1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLocalFlagToCommLinkImagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'comm_link_images',
            function (Blueprint $table) {
                $table->boolean('local')->default(false)->after('alt');
                $table->string('dir')->nullable()->after('local');
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
        Schema::table(
            'comm_link_images',
            function (Blueprint $table) {
                $table->dropColumn('local');
                $table->dropColumn('dir');
            }
        );
    }
}
