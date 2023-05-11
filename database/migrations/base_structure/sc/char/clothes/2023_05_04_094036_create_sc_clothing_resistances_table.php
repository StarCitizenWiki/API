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
        Schema::create('sc_clothing_resistances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('clothing_id');
            $table->string('type');
            $table->double('multiplier')->nullable();
            $table->double('threshold')->nullable();
            $table->timestamps();

            $table->foreign('clothing_id', 'fK_sc_c_res_clothing_id')
                ->references('id')
                ->on('sc_clothes')
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
        Schema::dropIfExists('sc_clothing_resistances');
    }
};
