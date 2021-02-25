<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMsrpAttributeToVehiclesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::table(
            'vehicles',
            function (Blueprint $table) {
                $table->unsignedInteger('msrp')
                    ->nullable()
                    ->after('chassis_id');
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
        Schema::table(
            'vehicles',
            function (Blueprint $table) {
                $table->dropColumn('msrp');
            }
        );
    }
}
