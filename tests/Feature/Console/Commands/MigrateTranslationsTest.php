<?php

declare(strict_types=1);

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('migrates translations from the source connection to the destination connection', function (): void {
    $originalDefaultConnection = config('database.default');
    $originalConnections = config('database.connections');

    try {
        config([
            'database.default' => 'to_test',
            'database.connections.from_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'database.connections.to_test' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
        ]);

        $simpleTables = [
            ['parent' => 'shipmatrix_production_statuses', 'translation' => 'production_status_translations', 'fk' => 'production_status_id'],
            ['parent' => 'shipmatrix_production_notes', 'translation' => 'production_note_translations', 'fk' => 'production_note_id'],
            ['parent' => 'shipmatrix_vehicle_sizes', 'translation' => 'vehicle_size_translations', 'fk' => 'size_id'],
            ['parent' => 'shipmatrix_vehicle_types', 'translation' => 'vehicle_type_translations', 'fk' => 'type_id'],
            ['parent' => 'shipmatrix_vehicle_foci', 'translation' => 'vehicle_foci_translations', 'fk' => 'focus_id'],
            ['parent' => 'galactapedia_articles', 'translation' => 'galactapedia_article_translations', 'fk' => 'galactapedia_article_id'],
            ['parent' => 'starmap_starsystems', 'translation' => 'starsystem_translations', 'fk' => 'starsystem_id'],
            ['parent' => 'starmap_celestial_objects', 'translation' => 'celestial_object_translations', 'fk' => 'celestial_object_id'],
            ['parent' => 'shipmatrix_vehicles', 'translation' => 'vehicle_translations', 'fk' => 'vehicle_id'],
            ['parent' => 'comm_links', 'translation' => 'comm_link_translations', 'fk' => 'comm_link_id'],
        ];

        foreach ($simpleTables as $definition) {
            Schema::connection('to_test')->create($definition['parent'], function (Blueprint $table): void {
                $table->unsignedBigInteger('id')->primary();
                $table->text('translation')->nullable();
            });

            Schema::connection('from_test')->create($definition['translation'], function (Blueprint $table) use ($definition): void {
                $table->id();
                $table->unsignedBigInteger($definition['fk']);
                $table->string('locale_code');
                $table->text('translation')->nullable();
            });
        }

        Schema::connection('to_test')->create('shipmatrix_manufacturers', function (Blueprint $table): void {
            $table->unsignedBigInteger('id')->primary();
            $table->text('known_for')->nullable();
            $table->text('description')->nullable();
        });

        Schema::connection('from_test')->create('manufacturer_translations', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('manufacturer_id');
            $table->string('locale_code');
            $table->text('known_for')->nullable();
            $table->text('description')->nullable();
        });

        DB::connection('to_test')->table('shipmatrix_vehicles')->insert([
            'id' => 1,
            'translation' => null,
        ]);

        DB::connection('from_test')->table('vehicle_translations')->insert([
            ['vehicle_id' => 1, 'locale_code' => 'en', 'translation' => 'Ship'],
            ['vehicle_id' => 1, 'locale_code' => 'fr', 'translation' => 'Vaisseau'],
            ['vehicle_id' => 1, 'locale_code' => 'es', 'translation' => ''],
        ]);

        DB::connection('to_test')->table('shipmatrix_manufacturers')->insert([
            'id' => 1,
            'known_for' => null,
            'description' => null,
        ]);

        DB::connection('from_test')->table('manufacturer_translations')->insert([
            ['manufacturer_id' => 1, 'locale_code' => 'en', 'known_for' => 'Engines', 'description' => null],
            ['manufacturer_id' => 1, 'locale_code' => 'fr', 'known_for' => null, 'description' => 'Description'],
        ]);

        $this->artisan('data:migrate-translations', [
            '--from' => 'from_test',
            '--to' => 'to_test',
            '--chunk' => 1,
        ])->assertExitCode(0);

        $vehicle = DB::connection('to_test')->table('shipmatrix_vehicles')->where('id', 1)->first();
        expect(json_decode((string) $vehicle->translation, true))->toBe([
            'en' => 'Ship',
            'fr' => 'Vaisseau',
        ]);

        $manufacturer = DB::connection('to_test')->table('shipmatrix_manufacturers')->where('id', 1)->first();
        expect(json_decode((string) $manufacturer->known_for, true))->toBe([
            'en' => 'Engines',
        ]);
        expect(json_decode((string) $manufacturer->description, true))->toBe([
            'fr' => 'Description',
        ]);
    } finally {
        DB::purge('from_test');
        DB::purge('to_test');

        config([
            'database.default' => $originalDefaultConnection,
            'database.connections' => $originalConnections,
        ]);
    }
});
