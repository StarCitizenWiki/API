<?php

use App\Models\Game\VehicleData;
use App\Support\Filters\FilterCache;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('game_vehicle_data', static function (Blueprint $table): void {
            $table->double('length')->nullable();
            $table->double('width')->nullable();
            $table->double('height')->nullable();

            $table->unsignedInteger('mass_total')->nullable();
            $table->double('speed_scm')->nullable();
            $table->unsignedInteger('speed_max')->nullable();
            $table->double('cargo_capacity')->nullable();
            $table->unsignedInteger('crew_min')->nullable();
            $table->unsignedInteger('crew_max')->nullable();
            $table->unsignedInteger('health')->nullable();
            $table->unsignedInteger('shield_hp')->nullable();
            $table->unsignedInteger('armor_health')->nullable();

            $table->string('shield_face_type', 50)->nullable();

            $table->double('vehicle_inventory')->nullable();

            $table->unsignedInteger('cross_section_length')->nullable();
            $table->unsignedInteger('cross_section_width')->nullable();
            $table->unsignedInteger('cross_section_height')->nullable();

            $table->unsignedInteger('signature_ir_quantum')->nullable();
            $table->unsignedInteger('signature_ir_shields')->nullable();
            $table->unsignedInteger('signature_em_quantum')->nullable();
            $table->unsignedInteger('signature_em_shields')->nullable();

            $table->string('max_medical_tier', 10)->nullable();
        });

        $driver = DB::connection()->getDriverName();

        if ($driver === 'sqlite') {
            DB::statement("
                UPDATE game_vehicle_data
                SET length = CAST(json_extract(data, '$.Length') AS REAL),
                    width = CAST(json_extract(data, '$.Width') AS REAL),
                    height = CAST(json_extract(data, '$.Height') AS REAL)
            ");

            DB::statement("
                UPDATE game_vehicle_data SET
                    mass_total = CAST(json_extract(data, '$.MassTotal') AS INTEGER),
                    speed_scm = CAST(json_extract(data, '$.FlightCharacteristics.Speeds.Scm') AS REAL),
                    speed_max = CAST(json_extract(data, '$.FlightCharacteristics.Speeds.Max') AS INTEGER),
                    cargo_capacity = CAST(json_extract(data, '$.Cargo') AS REAL),
                    crew_min = CAST(json_extract(data, '$.Crew') AS INTEGER),
                    crew_max = CAST(json_extract(data, '$.Crew') AS INTEGER),
                    health = CAST(json_extract(data, '$.Health') AS INTEGER),
                    shield_hp = CAST(json_extract(data, '$.ShieldsTotal.Hp') AS INTEGER),
                    shield_face_type = json_extract(data, '$.ShieldController.FaceType'),
                    armor_health = CAST(json_extract(data, '$.Armor.Health') AS INTEGER),
                    vehicle_inventory = CAST(json_extract(data, '$.Stowage') AS REAL),
                    cross_section_length = CAST(json_extract(data, '$.CrossSection.X') AS INTEGER),
                    cross_section_width = CAST(json_extract(data, '$.CrossSection.Y') AS INTEGER),
                    cross_section_height = CAST(json_extract(data, '$.CrossSection.Z') AS INTEGER),
                    signature_ir_quantum = CAST(json_extract(data, '$.Emission.IrQuantum') AS INTEGER),
                    signature_ir_shields = CAST(json_extract(data, '$.Emission.IrShields') AS INTEGER),
                    signature_em_quantum = CAST(json_extract(data, '$.Emission.EmQuantum') AS INTEGER),
                    signature_em_shields = CAST(json_extract(data, '$.Emission.EmShields') AS INTEGER)
            ");
        } else {
            DB::statement("
                UPDATE game_vehicle_data
                SET length = (data #>> '{Length}')::double precision,
                    width = (data #>> '{Width}')::double precision,
                    height = (data #>> '{Height}')::double precision
            ");

            DB::statement("
                UPDATE game_vehicle_data SET
                    mass_total = (data #>> '{MassTotal}')::integer,
                    speed_scm = (data #>> '{FlightCharacteristics,Speeds,Scm}')::double precision,
                    speed_max = (data #>> '{FlightCharacteristics,Speeds,Max}')::integer,
                    cargo_capacity = (data #>> '{Cargo}')::double precision,
                    crew_min = (data #>> '{Crew}')::integer,
                    crew_max = (data #>> '{Crew}')::integer,
                    health = (data #>> '{Health}')::integer,
                    shield_hp = (data #>> '{ShieldsTotal,Hp}')::integer,
                    shield_face_type = data #>> '{ShieldController,FaceType}',
                    armor_health = (data #>> '{Armor,Health}')::integer,
                    vehicle_inventory = (data #>> '{Stowage}')::double precision,
                    cross_section_length = (data #>> '{CrossSection,X}')::integer,
                    cross_section_width = (data #>> '{CrossSection,Y}')::integer,
                    cross_section_height = (data #>> '{CrossSection,Z}')::integer,
                    signature_ir_quantum = (data #>> '{Emission,IrQuantum}')::integer,
                    signature_ir_shields = (data #>> '{Emission,IrShields}')::integer,
                    signature_em_quantum = (data #>> '{Emission,EmQuantum}')::integer,
                    signature_em_shields = (data #>> '{Emission,EmShields}')::integer
            ");
        }

        // Backfill max_medical_tier via PHP (29 vehicles have medical beds)
        VehicleData::whereNotNull('data->Seating->MedicalBeds')
            ->chunkById(100, function ($vehicles): void {
                foreach ($vehicles as $vehicle) {
                    $vehicle->max_medical_tier = $this->resolveMaxMedicalTier(
                        Arr::get($vehicle->data, 'Seating.MedicalBeds', [])
                    );
                    $vehicle->save();
                }
            });

        FilterCache::bust(FilterCache::NAMESPACE_VEHICLES);
    }

    /**
     * Resolve the highest medical tier from a list of medical beds.
     *
     * @param  array<int, array{Tier?: string}>  $medicalBeds
     */
    private function resolveMaxMedicalTier(array $medicalBeds): ?string
    {
        if ($medicalBeds === []) {
            return null;
        }

        $best = null;
        $bestTier = 0;

        foreach ($medicalBeds as $bed) {
            $tierLabel = $bed['Tier'] ?? null;

            if (! is_string($tierLabel) || $tierLabel === '') {
                continue;
            }

            $tier = (int) ltrim($tierLabel, 'T');

            if ($tier > $bestTier) {
                $bestTier = $tier;
                $best = $tierLabel;
            }
        }

        return $best;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_vehicle_data', function (Blueprint $table): void {
            $table->dropColumn([
                'length', 'width', 'height',
                'mass_total', 'speed_scm', 'speed_max', 'cargo_capacity',
                'crew_min', 'crew_max',
                'health', 'shield_hp', 'shield_face_type', 'armor_health',
                'vehicle_inventory', 'cross_section_length', 'cross_section_width',
                'cross_section_height', 'signature_ir_quantum', 'signature_ir_shields',
                'signature_em_quantum', 'signature_em_shields', 'max_medical_tier',
            ]);
        });
    }
};
