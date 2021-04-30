<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateScUnpackedShipShieldsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('star_citizen_unpacked_ship_shields', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ship_item_id');
            $table->string('uuid')->unique();
            $table->unsignedDouble('max_shield_health');
            $table->unsignedDouble('max_shield_regen');
            $table->unsignedDouble('decay_ratio');
            $table->unsignedDouble('downed_regen_delay');
            $table->unsignedDouble('damage_regen_delay');
            $table->unsignedDouble('max_reallocation');
            $table->unsignedDouble('reallocation_rate');
            $table->unsignedDouble('shield_hardening_factor');
            $table->unsignedDouble('shield_hardening_duration');
            $table->unsignedDouble('shield_hardening_cooldown');
            $table->timestamps();

            $table->foreign('ship_item_id', 'shields_ship_item_id')
                ->references('id')
                ->on('star_citizen_unpacked_ship_items')
                ->onDelete('cascade');

            $table->foreign('uuid', 'shields_ship_item_uuid')
                ->references('uuid')
                ->on('star_citizen_unpacked_ship_items')
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
        Schema::dropIfExists('star_citizen_unpacked_ship_shields');
    }
}
