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
        Schema::create('star_citizen_unpacked_item_ports', static function (Blueprint $table) {
            $table->id();
            $table->string('item_uuid');

            $table->string('name');
            $table->string('display_name');
            $table->string('position')->nullable();
            $table->unsignedInteger('min_size')->nullable();
            $table->unsignedInteger('max_size')->nullable();
            $table->timestamps();

            $table->foreign('item_uuid', 'item_id_port_foreign')
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
    public function down(): void
    {
        Schema::dropIfExists('star_citizen_unpacked_item_ports');
    }
};
