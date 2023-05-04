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
        Schema::create('sc_item_translations', function (Blueprint $table) {
            $table->id();
            $table->char('locale_code', 5);
            $table->string('key');
            $table->text('translation');
            $table->timestamps();

            $table->unique(['locale_code', 'key'], 'u_sc_i_tra_locale_code_key');

            $table->foreign('locale_code', 'fk_sc_i_tra_locale')
                ->references('locale_code')
                ->on('languages');
//
//            $table->foreign('item_uuid', 'fk_sc_i_tra_item_uuid')
//                ->references('uuid')
//                ->on('sc_items')
//                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_item_translations');
    }
};
