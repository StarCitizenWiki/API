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
        Schema::create('sc_items', static function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('name');
            $table->string('type')->nullable();
            $table->string('sub_type')->nullable();
            $table->unsignedBigInteger('manufacturer_id');
            $table->unsignedInteger('size')->nullable();
            $table->string('class_name')->nullable();
            $table->string('version');
            $table->timestamps();

            $table->foreign('manufacturer_id', 'fk_sc_ite_manufacturer_id')
                ->references('id')
                ->on('sc_manufacturers')
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
        Schema::dropIfExists('sc_items');
    }
};
