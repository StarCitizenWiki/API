<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('vehicle_loaners', static function (Blueprint $table) {
            $table->unsignedInteger('vehicle_id');
            $table->unsignedInteger('loaner_id');
            $table->string('version');

            $table->foreign('vehicle_id', 'fk_vehicle_l_vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onDelete('cascade');

            $table->foreign('loaner_id', 'fk_vehicle_l_loaner_id')
                ->references('id')
                ->on('vehicles')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_loaners');
    }
};
