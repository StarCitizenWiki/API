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
        Schema::create('sc_vehicles', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('shipmatrix_id');
            $table->uuid()->unique();
            $table->string('class_name');
            $table->string('name');
            $table->string('career');
            $table->string('role');

            $table->boolean('is_ship')->default(true);

            $table->unsignedInteger('size')->nullable();
            $table->unsignedInteger('crew')->nullable();
            $table->unsignedBigInteger('mass')->nullable();

            $table->unsignedDouble('scm_speed')->nullable();
            $table->unsignedDouble('max_speed')->nullable();

            $table->unsignedDouble('zero_to_scm')->nullable();
            $table->unsignedDouble('zero_to_max')->nullable();

            $table->unsignedDouble('scm_to_zero')->nullable();
            $table->unsignedDouble('max_to_zero')->nullable();

            $table->unsignedDouble('claim_time')->nullable();
            $table->unsignedDouble('expedite_time')->nullable();
            $table->unsignedDouble('expedite_cost')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_vehicles');
    }
};
