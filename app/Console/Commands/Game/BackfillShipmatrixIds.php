<?php

declare(strict_types=1);

namespace App\Console\Commands\Game;

use App\Models\Game\VehicleData;
use App\Services\Game\VehicleMatchingService;
use Illuminate\Console\Command;

class BackfillShipmatrixIds extends Command
{
    protected $signature = 'game:backfill-shipmatrix-ids
                            {--game-version= : Specific game version code}
                            {--dry-run : Preview changes without updating}
                            {--limit= : Limit number of records to process}';

    protected $description = 'Backfill shipmatrix_id for game_vehicle_data records';

    public function __construct(
        private readonly VehicleMatchingService $matcher
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $query = VehicleData::query()
            ->whereNull('shipmatrix_id')
            ->with(['manufacturer', 'gameVersion', 'vehicle']);

        // Filter by game version if specified
        if ($version = $this->option('game-version')) {
            $query->whereHas('gameVersion', fn ($q) => $q->where('code', $version));
        }

        // Apply limit if specified
        if ($limit = $this->option('limit')) {
            $query->limit((int) $limit);
        }

        $vehicles = $query->get();

        if ($vehicles->isEmpty()) {
            $this->info('No unmatched vehicles found!');

            return self::SUCCESS;
        }

        $this->info("Found {$vehicles->count()} unmatched vehicles");

        $matched = 0;
        $failed = 0;
        $updates = []; // Collect for batch update

        foreach ($vehicles as $vehicle) {
            // Build payload for matching
            $payload = [
                'UUID' => $vehicle->vehicle->uuid ?? null,
                'Name' => $vehicle->name,
                'ClassName' => $vehicle->class_name,
                'Manufacturer' => [
                    'Code' => $vehicle->manufacturer?->name_short,
                    'Name' => $vehicle->manufacturer?->name,
                ],
            ];

            $shipmatrixId = $this->matcher->findMatch($payload);

            if ($shipmatrixId !== null) {
                $updates[$vehicle->id] = $shipmatrixId;
                $matched++;
                $this->line("✓ Matched: {$vehicle->name}");
            } else {
                $failed++;
                $this->line("✗ Failed: {$vehicle->name}");
            }
        }

        // Batch update all matched records
        if (! $this->option('dry-run') && ! empty($updates)) {
            foreach ($updates as $id => $shipmatrixId) {
                VehicleData::where('id', $id)->update(['shipmatrix_id' => $shipmatrixId]);
            }
        }

        $this->newLine();
        $this->info("Summary: {$matched} matched, {$failed} failed");

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN - No changes made');
        }

        if ($failed > 0) {
            $this->warn("Run 'php artisan game:review-vehicle-matches' to manually match failed vehicles");
        }

        return self::SUCCESS;
    }
}
