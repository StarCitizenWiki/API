<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedPersonalWeaponsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_personal_weapons', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('weapon_type')->nullable();
            $table->string('weapon_class')->nullable();

            $table->unsignedDouble('effective_range')->default(0);
            $table->string('rof')->default(0);

            $table->string('version');
            $table->timestamps();

            $table->foreign('uuid', 'personal_weapon_item_uuid')
                ->references('uuid')
                ->on('star_citizen_unpacked_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('star_citizen_unpacked_personal_weapons');
    }
}
