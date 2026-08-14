<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // cig_id is unique by schema definition but the index was dropped on prod at some point.
        if (! Schema::hasIndex('comm_links', 'comm_links_cig_id_unique')) {
            Schema::table('comm_links', function (Blueprint $table) {
                $table->unique('cig_id', 'comm_links_cig_id_unique');
            });
        }

        Schema::table('starmap_jumppoints', function (Blueprint $table) {
            if (! Schema::hasIndex('starmap_jumppoints', 'starmap_jumppoints_entry_id_index')) {
                $table->index('entry_id', 'starmap_jumppoints_entry_id_index');
            }

            if (! Schema::hasIndex('starmap_jumppoints', 'starmap_jumppoints_exit_id_index')) {
                $table->index('exit_id', 'starmap_jumppoints_exit_id_index');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comm_links', function (Blueprint $table) {
            $table->dropIndex('comm_links_cig_id_unique');
        });

        Schema::table('starmap_jumppoints', function (Blueprint $table) {
            $table->dropIndex('starmap_jumppoints_entry_id_index');
            $table->dropIndex('starmap_jumppoints_exit_id_index');
        });
    }
};
