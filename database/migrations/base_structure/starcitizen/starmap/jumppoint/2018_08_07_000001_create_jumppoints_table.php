<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJumppointsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create(
            'jumppoints',
            function (Blueprint $table) {
                $table->id('id');
                $table->unsignedBigInteger('cig_id');
                $table->string('direction');
                $table->unsignedBigInteger('entry_id');
                $table->unsignedBigInteger('exit_id');
                $table->string('name')->nullable();
                $table->string('size');

                $table->string('entry_status')->nullable();
                $table->string('exit_status')->nullable();

                $table->timestamps();

                $table->unique('cig_id');

                $table->foreign('entry_id')
                    ->references('cig_id')
                    ->on('celestial_objects');
                $table->foreign('exit_id')
                    ->references('cig_id')
                    ->on('celestial_objects');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('jumppoints');
    }
}
