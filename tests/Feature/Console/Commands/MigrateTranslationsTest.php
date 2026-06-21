<?php

declare(strict_types=1);

use App\Models\StarCitizen\ShipMatrix\Manufacturer;
use App\Models\StarCitizen\ShipMatrix\ProductionNote;
use App\Models\StarCitizen\ShipMatrix\ProductionStatus;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Size;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Type;
use App\Models\StarCitizen\ShipMatrix\Vehicle\Vehicle;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function createTranslationTables(string $connection): array
{
    $translationTables = [
        ['name' => 'production_status_translations', 'foreign_key' => 'production_status_id', 'columns' => ['translation']],
        ['name' => 'production_note_translations', 'foreign_key' => 'production_note_id', 'columns' => ['translation']],
        ['name' => 'vehicle_size_translations', 'foreign_key' => 'size_id', 'columns' => ['translation']],
        ['name' => 'vehicle_type_translations', 'foreign_key' => 'type_id', 'columns' => ['translation']],
        ['name' => 'vehicle_focus_translations', 'foreign_key' => 'focus_id', 'columns' => ['translation']],
        ['name' => 'galactapedia_article_translations', 'foreign_key' => 'article_id', 'columns' => ['translation']],
        ['name' => 'starsystem_translations', 'foreign_key' => 'starsystem_id', 'columns' => ['translation']],
        ['name' => 'celestial_object_translations', 'foreign_key' => 'celestial_object_id', 'columns' => ['translation']],
        ['name' => 'vehicle_translations', 'foreign_key' => 'vehicle_id', 'columns' => ['translation']],
        ['name' => 'comm_link_translations', 'foreign_key' => 'comm_link_id', 'columns' => ['translation']],
        ['name' => 'manufacturer_translations', 'foreign_key' => 'manufacturer_id', 'columns' => ['known_for', 'description']],
    ];

    foreach ($translationTables as $definition) {
        Schema::connection($connection)->dropIfExists($definition['name']);

        Schema::connection($connection)->create($definition['name'], function (Blueprint $table) use ($definition): void {
            $table->id();
            $table->unsignedBigInteger($definition['foreign_key']);
            $table->string('locale_code');

            foreach ($definition['columns'] as $column) {
                $table->text($column)->nullable();
            }
        });
    }

    return $translationTables;
}

function dropTranslationTables(string $connection, array $translationTables): void
{
    foreach ($translationTables as $definition) {
        Schema::connection($connection)->dropIfExists($definition['name']);
    }
}

function seedReferenceData(): array
{
    $status = ProductionStatus::factory()->create(['slug' => 'operational']);
    $note = ProductionNote::factory()->create();
    $size = Size::factory()->create(['slug' => 'small']);
    $type = Type::factory()->create(['slug' => 'fighter']);
    $manufacturer = Manufacturer::factory()->create([
        'cig_id' => 1001,
        'name' => 'Test Manufacturer',
        'name_short' => 'TEST',
    ]);
    $vehicle = Vehicle::factory()->create([
        'cig_id' => 2001,
        'name' => 'Test Ship',
        'slug' => 'test-ship',
        'manufacturer_id' => $manufacturer->id,
        'production_status_id' => $status->id,
        'production_note_id' => $note->id,
        'size_id' => $size->id,
        'type_id' => $type->id,
        'chassis_id' => 3001,
    ]);

    return compact('status', 'note', 'size', 'type', 'manufacturer', 'vehicle');
}

it('translates simple text columns', function (): void {
    $connection = (string) config('database.default');
    $translationTables = createTranslationTables($connection);

    try {
        $data = seedReferenceData();

        DB::connection($connection)->table('production_status_translations')->insert([
            ['production_status_id' => $data['status']->id, 'locale_code' => 'en', 'translation' => 'Operational'],
            ['production_status_id' => $data['status']->id, 'locale_code' => 'fr', 'translation' => 'Opérationnel'],
        ]);

        DB::connection($connection)->table('vehicle_translations')->insert([
            ['vehicle_id' => $data['vehicle']->id, 'locale_code' => 'en', 'translation' => 'Test Ship'],
            ['vehicle_id' => $data['vehicle']->id, 'locale_code' => 'de', 'translation' => 'Testschiff'],
        ]);

        $this->artisan('data:migrate-translations', [
            '--from' => $connection,
            '--to' => $connection,
            '--chunk' => 1,
        ])
            ->assertSuccessful()
            ->expectsOutputToContain('Migrating translations to JSON columns...')
            ->expectsOutputToContain('Translation migration complete.');

        expect(json_decode((string) DB::connection($connection)->table('shipmatrix_production_statuses')->where('id', $data['status']->id)->value('translation'), true))->toBe([
            'en' => 'Operational',
            'fr' => 'Opérationnel',
        ]);

        expect(json_decode((string) DB::connection($connection)->table('shipmatrix_vehicles')->where('id', $data['vehicle']->id)->value('translation'), true))->toBe([
            'en' => 'Test Ship',
            'de' => 'Testschiff',
        ]);
    } finally {
        dropTranslationTables($connection, $translationTables);
    }
});

it('skips empty-string translations', function (): void {
    $connection = (string) config('database.default');
    $translationTables = createTranslationTables($connection);

    try {
        $data = seedReferenceData();

        DB::connection($connection)->table('production_status_translations')->insert([
            ['production_status_id' => $data['status']->id, 'locale_code' => 'en', 'translation' => 'Operational'],
            ['production_status_id' => $data['status']->id, 'locale_code' => 'es', 'translation' => ''],
        ]);

        $this->artisan('data:migrate-translations', [
            '--from' => $connection,
            '--to' => $connection,
            '--chunk' => 1,
        ])->assertSuccessful();

        // Empty-string locale 'es' must not appear in the result
        expect(json_decode((string) DB::connection($connection)->table('shipmatrix_production_statuses')->where('id', $data['status']->id)->value('translation'), true))->toBe([
            'en' => 'Operational',
        ]);
    } finally {
        dropTranslationTables($connection, $translationTables);
    }
});

it('handles multi-column tables like manufacturer', function (): void {
    $connection = (string) config('database.default');
    $translationTables = createTranslationTables($connection);

    try {
        $data = seedReferenceData();

        DB::connection($connection)->table('manufacturer_translations')->insert([
            ['manufacturer_id' => $data['manufacturer']->id, 'locale_code' => 'en', 'known_for' => 'Engines', 'description' => null],
            ['manufacturer_id' => $data['manufacturer']->id, 'locale_code' => 'fr', 'known_for' => null, 'description' => 'Description'],
        ]);

        $this->artisan('data:migrate-translations', [
            '--from' => $connection,
            '--to' => $connection,
            '--chunk' => 1,
        ])->assertSuccessful();

        expect(json_decode((string) DB::connection($connection)->table('shipmatrix_manufacturers')->where('id', $data['manufacturer']->id)->value('known_for'), true))->toBe([
            'en' => 'Engines',
        ]);
        expect(json_decode((string) DB::connection($connection)->table('shipmatrix_manufacturers')->where('id', $data['manufacturer']->id)->value('description'), true))->toBe([
            'fr' => 'Description',
        ]);
    } finally {
        dropTranslationTables($connection, $translationTables);
    }
});